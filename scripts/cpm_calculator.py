from flask import Flask, request, jsonify, send_file
import networkx as nx
import os
import matplotlib.pyplot as plt
from flask_cors import CORS
import math

app = Flask(__name__)
CORS(app)

def calculate_style(num_nodes):
    """Menghitung parameter visual berdasarkan jumlah node"""
    return {
        'node_size': max(300, 3000 - num_nodes * 25),
        'font_size': max(6, 12 - int(num_nodes / 10)),
        'arrow_size': max(8, 20 - int(num_nodes / 5)),
        'edge_width': max(0.5, 2.0 - num_nodes / 50),
        'fig_width': max(10, num_nodes / 5),
        'fig_height': max(6, num_nodes / 8),
        'title_size': max(10, 16 - int(num_nodes / 20))
    }

@app.route('/run-cpm', methods=['POST'])
def run_cpm():
    try:
        data = request.get_json()
        G = nx.DiGraph()
        
        # Bangun graph
        for task_name, info in data.items():
            G.add_node(task_name, durasi=info['durasi'])
            for pre_task in info['syarat']:
                G.add_edge(pre_task, task_name, weight=info['durasi'])

        if not nx.is_directed_acyclic_graph(G):
            return jsonify({"error": "Graph contains a cycle!"}), 400

        critical_path = nx.dag_longest_path(G, weight='durasi')
        num_nodes = len(G.nodes())
        style = calculate_style(num_nodes)

        # Setup figure dinamis
        plt.figure(figsize=(style['fig_width'], style['fig_height']), facecolor='none')
        ax = plt.gca()
        ax.set_facecolor('none')

        # Pilih layout berdasarkan jumlah node
        if num_nodes > 30:
            pos = nx.kamada_kawai_layout(G)
        else:
            pos = nx.shell_layout(G)

        # Draw graph dengan parameter dinamis
        nx.draw(
            G,
            pos,
            with_labels=True,
            labels={node: node for node in G.nodes()},
            node_color='#4CAF50',
            node_size=style['node_size'],
            font_size=style['font_size'],
            font_weight='bold',
            arrowsize=style['arrow_size'],
            edgecolors='#2E7D32',
            width=style['edge_width']
        )

        # Edge labels
        edge_labels = {(u, v): G[u][v]['weight'] for u, v in G.edges}
        nx.draw_networkx_edge_labels(
            G, pos, 
            edge_labels=edge_labels,
            font_color='#1A237E',
            font_size=max(5, style['font_size'] - 2)
        )

        # Critical path
        path_edges = list(zip(critical_path[:-1], critical_path[1:]))
        nx.draw_networkx_edges(
            G, pos,
            edgelist=path_edges,
            edge_color='#D32F2F',
            width=style['edge_width'] * 2
        )

        plt.title('Critical Path Method (CPM) Graph', 
                fontsize=style['title_size'],
                pad=20)
        plt.axis('off')
        
        img_path = "output_graph.png"
        plt.savefig(img_path, bbox_inches='tight', dpi=300)
        plt.close()

        return jsonify({"image": "http://127.0.0.1:5000/get-image"})
    
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/get-image', methods=['GET'])
def get_image():
    return send_file("c://laragon/www/Website-CPM/output_graph.png", mimetype="image/png")

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)