import os
import pymysql
import json
from dotenv import load_dotenv
from langchain_groq import ChatGroq
from langchain.schema import HumanMessage
import re

def load_api_key():
    """Memuat API key dari file .env"""
    load_dotenv()
    return os.getenv("GROQ_API_KEY")

def initialize_chat(model_name="deepseek-r1-distill-llama-70b", temperature=0.9):
    """Menginisialisasi model Groq dengan LangChain"""
    api_key = load_api_key()
    if not api_key:
        raise ValueError("GROQ_API_KEY tidak ditemukan! Pastikan sudah ada di file .env.")
    
    return ChatGroq(
        temperature=temperature,
        model_name=model_name,
        groq_api_key=api_key
    )

def get_nodes_only(idproject):
    """Mengambil hanya daftar node dari database, termasuk nama aktivitas."""
    db_config = {
        "host": "localhost",
        "user": "root",
        "password": "",
        "database": "web-cpm"
    }
    
    conn = pymysql.connect(
        host=db_config["host"],
        user=db_config["user"],
        password=db_config["password"],
        database=db_config["database"],
        cursorclass=pymysql.cursors.DictCursor
    )
    
    query = """
    SELECT 
        n.idnode AS node_id, 
        n.activity AS node_activity,
        n.durasi
    FROM nodes n
    JOIN sub_activity sa ON n.id_sub_activity = sa.idsub_activity
    JOIN activity a ON sa.idactivity = a.idactivity
    JOIN project p ON a.idproject = p.idproject
    WHERE p.idproject = %s;
    """
    
    try:
        with conn.cursor() as cursor:
            cursor.execute(query, (idproject,))
            results = cursor.fetchall()

        nodes = [
            {
                "node_id": row["node_id"],
                "node_activity": row["node_activity"],  
                "durasi": row["durasi"]
            }
            for row in results
        ]

        return json.dumps({"nodes": nodes}, indent=4)

    except Exception as e:
        return json.dumps({"error": str(e)})

    finally:
        conn.close()

def ask_groq_for_predecessor(nodes_json):
    """Menggunakan AI untuk menentukan predecessor berdasarkan node_id."""
    chat = initialize_chat()
    
    prompt = f"""
    Berikut adalah daftar node proyek:

    {nodes_json}

    Ketentuan:
    1. Setiap node harus memiliki "node_id" dan "predecessor".
    2. "predecessor" berisi array yang menampung node_id predecessor.
    3. Jika sebuah node tidak memiliki pekerjaan pendahulu, maka "predecessor" = [].
    4. Hasil akhirnya **hanya** dalam format JSON berikut:

    **Format yang Saya Inginkan:**
    {{
        "predecessors": [
            {{"node_id": 56, "predecessor": []}},
            {{"node_id": 57, "predecessor": [20]}},
            {{"node_id": 58, "predecessor": [11]}},
            {{"node_id": 59, "predecessor": [40, 58]}},
            {{"node_id": 56, "predecessor": [19, 1]}}
        ]
    }}

    Pastikan hanya mengembalikan JSON tanpa teks tambahan.
    """

    response = chat([HumanMessage(content=prompt)])
    return response.content

def extract_and_parse_json(text):
    """Membersihkan output AI dan mengekstrak JSON dari respons AI."""
    text_no_think = re.sub(r"<think>.*?</think>", "", text, flags=re.DOTALL)
    
    pattern = r'(?s)```(?:json)?\s*(\{.*?\})\s*```'
    match = re.search(pattern, text_no_think)
    
    if match:
        json_str = match.group(1).strip()
        try:
            data = json.loads(json_str)
            return data
        except json.JSONDecodeError as e:
            print("Gagal parse JSON", e)
            return None
    else:
        print("Blok JSON tidak ditemukan.")
        return None

def check_predecessor_exists(cursor, node_core, node_cabang):
    """Memeriksa apakah node_core dan node_cabang sudah ada di tabel predecessor."""
    sql = "SELECT COUNT(*) FROM predecessor WHERE node_core = %s AND node_cabang = %s"
    cursor.execute(sql, (node_core, node_cabang))
    count = cursor.fetchone()["COUNT(*)"]
    return count > 0  # True jika sudah ada, False jika belum ada

if __name__ == "__main__":
    id_project = input("Masukkan ID proyek: ")

    # Ambil hanya daftar nodes
    nodes_json = get_nodes_only(id_project)
    print("\nDaftar node diambil. Meminta AI untuk menentukan predecessor berdasarkan node saja...\n")

    # Minta AI untuk menentukan predecessor
    predecessors_nodes = ask_groq_for_predecessor(nodes_json)
    print(predecessors_nodes)

    # Parse hasil JSON AI
    result = extract_and_parse_json(predecessors_nodes)

    if result is None:
        print("Gagal memproses data predecessor.")
        exit()

    # Koneksi ke database
    db = pymysql.connect(
        host="localhost",
        user="root",
        password="",
        db="web-cpm",
        cursorclass=pymysql.cursors.DictCursor
    )
    
    cursor = db.cursor()

    # Loop setiap item di 'predecessors'
    for item in result["predecessors"]:
        node_core = item["node_id"]
        predecessors = item["predecessor"]

        for p in predecessors:
            # Cek apakah data sudah ada
            if not check_predecessor_exists(cursor, node_core, p):
                sql = "INSERT INTO predecessor (node_core, node_cabang) VALUES (%s, %s)"
                cursor.execute(sql, (node_core, p))
                print(f"Inserted: node_core={node_core}, node_cabang={p}")
            else:
                print(f"Skipped (Already Exists): node_core={node_core}, node_cabang={p}")

    db.commit()
    cursor.close()
    db.close()

    print("\nPredecessor berdasarkan nodes berhasil disimpan ke database.")
