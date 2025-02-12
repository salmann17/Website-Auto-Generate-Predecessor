from flask import Flask, request, jsonify, send_file
import networkx as nx
import os
import matplotlib.pyplot as plt
from flask_cors import CORS
import time

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
            scale = 1
        elif figsize_input <= 20:
            scale = 2
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

        for task_name, info in tasks.items():  
            G.add_node(task_name, durasi=info['durasi'])
            for pre_task in info['syarat']:
                G.add_edge(pre_task, task_name, weight=info['durasi'])

        if not nx.is_directed_acyclic_graph(G):
            return jsonify({"error": "Graph contains a cycle!"}), 400

        critical_path = nx.dag_longest_path(G, weight='durasi')

        level_map = {}
        for node in nx.topological_sort(G):
            predecessors = list(G.predecessors(node))
            if predecessors:
                level_map[node] = max(level_map[p] for p in predecessors) + 1
            else:
                level_map[node] = 0  

        for node in G.nodes:
            if node not in level_map:
                level_map[node] = 0 

        first_nodes = [node for node in G.nodes if level_map[node] == 0]
        if first_nodes:
            for node in first_nodes:
                level_map[node] = -1  

        try:
            pos = nx.multipartite_layout(G, subset_key=lambda n: level_map[n], scale=scale)
        except Exception as e:
            print(f"⚠️ Multipartite Layout Error: {e}, menggunakan kamada_kawai_layout sebagai backup")
            pos = nx.kamada_kawai_layout(G)  

        node_count = len(G.nodes)
        figsize_x = 8 * scale
        figsize_y = 6 * scale
        plt.figure(figsize=(figsize_x, figsize_y), facecolor='white')

        ax = plt.gca()
        ax.set_facecolor('white')  

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

        edge_labels = {(u, v): G[u][v]['weight'] for u, v in G.edges}
        nx.draw_networkx_edge_labels(G, pos, edge_labels=edge_labels, font_color='blue', font_size=10)

        nx.draw_networkx_edges(
            G,
            pos,
            edgelist=G.edges,
            edge_color='gray',
            arrows=True,
            arrowsize=30,  
            width=2
        )

        path_edges = list(zip(critical_path[:-1], critical_path[1:]))
        nx.draw_networkx_edges(G, pos, edgelist=path_edges, edge_color='red', width=3, arrowsize=30)

        plt.title('Critical Path Method (CPM) Graph', fontsize=14, fontweight='bold')
        plt.axis('off')  
        plt.tight_layout()
        img_path = "output_graph.png"
        plt.savefig(img_path, dpi=300)
        plt.close()

        if not os.path.exists(img_path):
            print("❌ ERROR: Gambar tidak ditemukan setelah disimpan!")
            return jsonify({"error": "Gagal menyimpan gambar"}), 500
        else:
            print(f"✅ Gambar berhasil disimpan: {img_path}")

        return jsonify({
            "image": f"http://127.0.0.1:5000/get-image?t={int(time.time())}" 
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
