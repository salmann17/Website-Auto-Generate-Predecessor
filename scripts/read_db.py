import os
import pymysql
import json
from dotenv import load_dotenv
from langchain_groq import ChatGroq
from langchain.schema import HumanMessage
import re
import json

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

def chat_with_groq(prompt):
    """Menggunakan model Groq untuk menjawab pertanyaan"""
    chat = initialize_chat()
    messages = [HumanMessage(content=prompt)]
    response = chat(messages)
    return response.content

def get_project_hierarchy(idproject):
    """
    Mengambil data proyek dengan struktur hierarkis dari database.
    :param idproject: ID proyek yang ingin diambil
    :return: JSON string berisi struktur hierarkis proyek
    """
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
        p.idproject, p.nama AS project_name,
        a.idactivity, a.activity AS activity_name,
        sa.idsub_activity, sa.activity AS sub_activity_name,
        n.idnode, n.activity AS node_activity,
        n.durasi
    FROM project p
    JOIN activity a ON p.idproject = a.idproject
    JOIN sub_activity sa ON a.idactivity = sa.idactivity
    JOIN nodes n ON sa.idsub_activity = n.id_sub_activity
    WHERE p.idproject = %s;
    """
    
    try:
        with conn.cursor() as cursor:
            cursor.execute(query, (idproject,))
            results = cursor.fetchall()
        
        projects = {}
        for row in results:
            project_id = row["idproject"]
            activity_id = row["idactivity"]
            sub_activity_id = row["idsub_activity"]
            node_id = row["idnode"]

            if project_id not in projects:
                projects[project_id] = {
                    "project_name": row["project_name"],
                    "activities": {}
                }
            
            if activity_id not in projects[project_id]["activities"]:
                projects[project_id]["activities"][activity_id] = {
                    "activity_name": row["activity_name"],
                    "sub_activities": {}
                }
            
            if sub_activity_id not in projects[project_id]["activities"][activity_id]["sub_activities"]:
                projects[project_id]["activities"][activity_id]["sub_activities"][sub_activity_id] = {
                    "sub_activity_name": row["sub_activity_name"],
                    "nodes": []
                }
            
            projects[project_id]["activities"][activity_id]["sub_activities"][sub_activity_id]["nodes"].append({
                "node_id": node_id,
                "node_activity": row["node_activity"],
                "durasi": row["durasi"]
            })
        
        return json.dumps(projects, indent=4)
    
    except Exception as e:
        return json.dumps({"error": str(e)})
    
    finally:
        conn.close()
        
def get_nodes_only(idproject):
    """
    Mengambil hanya daftar node dari database, termasuk nama aktivitas.
    :param idproject: ID proyek yang ingin diambil
    :return: JSON array dengan daftar node beserta nama aktivitasnya
    """
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
                "node_activity": row["node_activity"],  # Menambahkan nama aktivitas
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
    """Menggunakan AI untuk menentukan predecessor hanya berdasarkan node_id."""
    chat = initialize_chat()
    
    prompt = f"""
    Berikut adalah daftar node proyek:

    {nodes_json}

    Ketentuan:
    1. Setiap node harus memiliki "node_id" dan "predecessor".
    2. "predecessor" berisi array (misalnya: [] atau [1, 2, 3]) yang menampung node_id predecessor.
    3. Jika sebuah node tidak memiliki pekerjaan pendahulu, maka "predecessor" = [].
    4. Anda diperbolehkan menentukan predecessor selain node_id yang langsung sebelum-nya (maksudnya, predecessor bisa node mana pun yang lebih awal, asalkan logis).
    5. Hasil akhirnya **hanya** dalam format JSON berikut:

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

    **Petunjuk Urutan Logis (Contoh Sederhana)**  
    - Pekerjaan struktur (misal: balok, kolom, pelat) harus menunggu pondasi selesai.  
    - Pemasangan bekisting harus sebelum pengecoran.  
    - Pekerjaan finishing (cat, keramik) menunggu pekerjaan struktur dan dinding selesai.  
    - Instalasi MEP dapat berjalan paralel, namun tetap menunggu sebagian struktur siap.
    """

    response = chat([HumanMessage(content=prompt)])
    return response.content

def extract_and_parse_json(text):
    # 1. Hapus blok <think> ... </think> menggunakan regex DOTALL agar '.*?' menjangkau multiple lines
    text_no_think = re.sub(r"<think>.*?</think>", "", text, flags=re.DOTALL)
    
    # 2. Cari blok JSON di antara triple backticks
    #    Regex (?s) = DOTALL agar '.' bisa match newline.
    #    Kita tangkap isi kurung kurawal { } (group 1) di antara backticks.
    pattern = r'(?s)```(?:json)?\s*(\{.*?\})\s*```'
    match = re.search(pattern, text_no_think)
    
    if match:
        json_str = match.group(1).strip()
        # 3. (Opsional) Parse menjadi dict Python
        try:
            data = json.loads(json_str)
            return data
        except json.JSONDecodeError as e:
            print("Gagal parse JSON", e)
            return None
    else:
        print("Blok JSON tidak ditemukan.")
        return None

if __name__ == "__main__":
    id_project = input("Masukkan ID proyek: ")
    
    # json_hierarchy = get_project_hierarchy(id_project)
    # print("Struktur proyek diambil. Meminta AI untuk menentukan predecessor...\n")
    # predecessors = ask_groq_for_predecessor(json_hierarchy)
    # print("\nPredecessor yang dihasilkan AI:\n", predecessors)
    
    # Ambil hanya daftar nodes (kode baru)
    nodes_json = get_nodes_only(id_project)
    print(nodes_json)
    print("\nDaftar node diambil. Meminta AI untuk menentukan predecessor berdasarkan node saja...\n")
    predecessors_nodes = ask_groq_for_predecessor(nodes_json)
    result = extract_and_parse_json(predecessors_nodes)
    
    db = pymysql.connect(
        host="localhost",
        user="root",
        password="",
        db="web-cpm"
    )
    
    cursor = db.cursor()
    
    # Loop setiap item di 'predecessors'
    for item in result["predecessors"]:
        node_core = item["node_id"]
        predecessors = item["predecessor"]

        # Jika predecessor-nya ada, buat INSERT untuk tiap predecessor
        for p in predecessors:
            sql = "INSERT INTO predecessor (node_core, node_cabang) VALUES (%s, %s)"
            cursor.execute(sql, (node_core, p))
            
    db.commit()
    cursor.close()
    db.close()
    
    # print("\nPredecessor berdasarkan nodes saja:\n", result)