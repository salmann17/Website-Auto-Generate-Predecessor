from flask import Flask, request, jsonify, send_file
import networkx as nx
import matplotlib.pyplot as plt
from flask_cors import CORS
import math
import textwrap

app = Flask(__name__)
CORS(app)

COLOR_SCHEME = {
    'background': 'none',
    'node': '#2C3E50',
    'node_border': '#34495E',
    'edge': '#95A5A6',
    'text': '#ECF0F1',
    'critical_path': '#E74C3C',
    'duration_text': '#27AE60'
}

def calculate_style(num_nodes, max_label_length):
    """Menghitung parameter visual dengan pertimbangan panjang label"""
    base_scale = math.log(num_nodes + 5)
    return {
        'node_size': max(800, 4000 - num_nodes*30 - max_label_length*10),
        'font_size': max(8, 14 - num_nodes//8 - max_label_length//4),
        'arrow_size': max(12, 25 - num_nodes//8),
        'edge_width': max(1.0, 2.5 - num_nodes/80),
        'fig_width': max(12, 6 + num_nodes//3 + max_label_length//10),
        'fig_height': max(8, 5 + num_nodes//4),
        'title_size': max(12, 18 - num_nodes//15),
        'wrap_width': max(15, 25 - num_nodes//10)
    }

def create_label(text, duration, wrap_width):
    """Membuat label dengan text wrapping"""
    wrapped = textwrap.fill(text, width=wrap_width)
    return f"{wrapped}\n({duration} hari)"

@app.route('/run-cpm', methods=['POST'])
def run_cpm():
    try:
        data = request.get_json()
        G = nx.DiGraph()
        max_label_length = 0
        
        # Bangun graph dengan label yang dirapikan
        for task_name, info in data.items():
            label_length = len(task_name)
            if label_length > max_label_length:
                max_label_length = label_length
                
            G.add_node(task_name, 
                      durasi=info['durasi'],
                      label=task_name)
            for pre_task in info['syarat']:
                G.add_edge(pre_task, task_name, weight=info['durasi'])

        if not nx.is_directed_acyclic_graph(G):
            return jsonify({"error": "Graph contains a cycle!"}), 400

        critical_path = nx.dag_longest_path(G, weight='durasi')
        num_nodes = len(G.nodes())
        style = calculate_style(num_nodes, max_label_length)

        # Setup figure
        plt.figure(figsize=(style['fig_width'], style['fig_height']), 
                 facecolor=COLOR_SCHEME['background'],
                 dpi=300,
                 layout='constrained')
        ax = plt.gca()
        ax.set_facecolor(COLOR_SCHEME['background'])
        
        # Hierarchical layout dengan penyesuaian
        pos = nx.multipartite_layout(G, subset_key="layer", align='horizontal')
        
        # Update labels dengan text wrapping
        labels = {node: create_label(node, G.nodes[node]['durasi'], style['wrap_width']) 
                for node in G.nodes()}

        # Draw elements
        nx.draw_networkx_nodes(
            G, pos,
            node_color=COLOR_SCHEME['node'],
            node_size=style['node_size'],
            edgecolors=COLOR_SCHEME['node_border'],
            linewidths=1.5,
            alpha=0.95
        )

        nx.draw_networkx_edges(
            G, pos,
            edge_color=COLOR_SCHEME['edge'],
            width=style['edge_width'],
            arrowsize=style['arrow_size'],
            arrowstyle='-|>,head_width=0.6,head_length=0.6',
            alpha=0.85
        )

        # Critical path
        critical_edges = list(zip(critical_path[:-1], critical_path[1:]))
        nx.draw_networkx_edges(
            G, pos,
            edgelist=critical_edges,
            edge_color=COLOR_SCHEME['critical_path'],
            width=style['edge_width']*2,
            style='dashed',
            alpha=0.95
        )

        nx.draw_networkx_labels(
            G, pos,
            labels=labels,
            font_size=style['font_size'],
            font_color=COLOR_SCHEME['text'],
            verticalalignment='center',
            horizontalalignment='center',
            font_family='DejaVu Sans',
            bbox=dict(
                facecolor=COLOR_SCHEME['node'],
                edgecolor=COLOR_SCHEME['node_border'],
                boxstyle='round,pad=0.3',
                alpha=0.9
            )
        )

        plt.title('Critical Path Method (CPM)',
                fontsize=style['title_size'],
                color=COLOR_SCHEME['text'],
                pad=20)
        plt.axis('off')

        img_path = "output_graph.png"
        plt.savefig(img_path, 
                  transparent=True,
                  bbox_inches='tight',
                  pad_inches=0.3,
                  dpi=300)
        plt.close()

        return jsonify({"image": "http://127.0.0.1:5000/get-image"})
    
    except Exception as e:
        return jsonify({"error": str(e)}), 500

def assign_layers(G):
    """Menentukan layer hierarki untuk tiap node"""
    layers = {}
    for node in nx.topological_sort(G):
        predecessors = list(G.predecessors(node))
        layers[node] = max([layers[p] for p in predecessors], default=-1) + 1
    nx.set_node_attributes(G, layers, "layer")
    return layers

@app.route('/get-image', methods=['GET'])
def get_image():
    return send_file("c://github/Website-CPM/output_graph.png", mimetype="image/png")

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)