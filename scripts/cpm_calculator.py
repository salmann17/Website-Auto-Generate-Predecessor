from flask import Flask, request, jsonify, send_file
import networkx as nx
import os
import matplotlib.pyplot as plt
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

@app.route('/run-cpm', methods=['POST'])
def run_cpm():
    try:
        data = request.get_json()
        print("\U0001F4E9 Data diterima dari Laravel:", data)
        tasks = data.get('tasks', {})
        figsize_input = data.get('figsize', 10)
        
        if figsize_input <= 10:
            scale = 0.3
        elif figsize_input <= 20:
            scale = 1
        elif figsize_input <= 30:
            scale = 3
        elif figsize_input <= 40:
            scale = 4
        elif figsize_input <= 50:
            scale = 5
        elif figsize_input <= 60:
            scale = 6
        elif figsize_input <= 70:
            scale = 7
        elif figsize_input <= 80:
            scale = 8
        elif figsize_input <= 90:
            scale = 9
        else:
            scale = 10  

        G = nx.DiGraph()

        # Tambahkan node dan edge berdasarkan data
        for task_name, info in tasks.items():  # Use tasks instead of data
            G.add_node(task_name, durasi=info['durasi'])
            for pre_task in info['syarat']:
                G.add_edge(pre_task, task_name, weight=info['durasi'])

        # Cek apakah ada siklus di dalam graph
        if not nx.is_directed_acyclic_graph(G):
            return jsonify({"error": "Graph contains a cycle!"}), 400

        # Cari jalur kritis
        critical_path = nx.dag_longest_path(G, weight='durasi')

        # **Menentukan level tiap node berdasarkan urutan topologi**
        level_map = {}
        for node in nx.topological_sort(G):
            predecessors = list(G.predecessors(node))
            if predecessors:
                level_map[node] = max(level_map[p] for p in predecessors) + 1
            else:
                level_map[node] = 0  # Node awal diberi level 0

        # Pastikan semua node mendapatkan level agar tidak terjadi error
        for node in G.nodes:
            if node not in level_map:
                level_map[node] = 0  # Default level jika tidak ditemukan

        # **Atur agar node pertama dimulai dari sebelah kiri**
        first_nodes = [node for node in G.nodes if level_map[node] == 0]
        if first_nodes:
            for node in first_nodes:
                level_map[node] = -1  # Pastikan node pertama lebih kiri dari lainnya

        # **Gunakan layout multipartite agar lebih rapi dari kiri ke kanan**
        try:
            pos = nx.multipartite_layout(G, subset_key=lambda n: level_map[n])
        except Exception as e:
            print(f"⚠️ Multipartite Layout Error: {e}, menggunakan kamada_kawai_layout sebagai backup")
            pos = nx.kamada_kawai_layout(G)  # Backup jika multipartite gagal

        # **FIGSIZE OTOMATIS BERUBAH SESUAI JUMLAH NODE**
        node_count = len(G.nodes)
        figsize_x = 8 * scale
        figsize_y = 6 * scale
        plt.figure(figsize=(figsize_x, figsize_y), facecolor='white')

        # **Konfigurasi tampilan grafik**
        ax = plt.gca()
        ax.set_facecolor('white')  

        # **Gambar node**
        nx.draw(
            G,
            pos,
            with_labels=True,
            labels={node: node for node in G.nodes()},
            node_color='lightblue',
            node_size=3500,
            font_size=12,
            font_color='black',
            font_weight='bold',
            linewidths=2,
            edgecolors='black'
        )

        # **Tambahkan label bobot pada edges (durasi aktivitas)**
        edge_labels = {(u, v): G[u][v]['weight'] for u, v in G.edges}
        nx.draw_networkx_edge_labels(G, pos, edge_labels=edge_labels, font_color='blue', font_size=10)

        # **Garis dan panah lebih jelas**
        nx.draw_networkx_edges(
            G,
            pos,
            edgelist=G.edges,
            edge_color='gray',
            arrows=True,
            arrowsize=30,  # Ukuran panah lebih besar
            width=2
        )

        # **Garis tebal untuk Critical Path**
        path_edges = list(zip(critical_path[:-1], critical_path[1:]))
        nx.draw_networkx_edges(G, pos, edgelist=path_edges, edge_color='red', width=3, arrowsize=30)

        # **Konfigurasi akhir plot**
        plt.title('Critical Path Method (CPM) Graph', fontsize=14, fontweight='bold')
        plt.axis('off')  
        plt.tight_layout()
        img_path = "output_graph.png"
        plt.savefig(img_path, dpi=300)
        plt.close()

        # **Validasi apakah gambar berhasil dibuat**
        if not os.path.exists(img_path):
            print("❌ ERROR: Gambar tidak ditemukan setelah disimpan!")
            return jsonify({"error": "Gagal menyimpan gambar"}), 500
        else:
            print(f"✅ Gambar berhasil disimpan: {img_path}")

        return jsonify({
            "image": "http://127.0.0.1:5000/get-image"
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/get-image', methods=['GET'])
def get_image():
    return send_file("c://github/Website-CPM/output_graph.png", mimetype="image/png")

@app.route('/', methods=['GET'])
def home():
    return jsonify({"message": "Flask API is running"}), 200

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
