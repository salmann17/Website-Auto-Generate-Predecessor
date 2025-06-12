from flask import Flask, request, jsonify
from flask_cors import CORS
import openpyxl
import io

app = Flask(__name__)
CORS(app)

def parse_row(row):
    no_col = str(row[0]).strip() if row[0] is not None else ''
    name = str(row[1]).strip() if row[1] else ''
    vol = row[2] if len(row) > 5 and row[5] else 0
    uom = str(row[3]).strip() if len(row) > 6 and row[6] else ''
    duration = row[5] if len(row) > 2 and row[2] else 0
    description = row[6] if len(row) > 3 and row[3] else ''
    total_price = row[4] if len(row) > 4 and row[4] else 0
    
    if no_col.isdigit():
        return {
            'type': 'activity',
            'name': name
        }
    elif no_col == '*':
        return {
            'type': 'sub_activity',
            'name': name
        }
    elif no_col == '-':
        return {
            'type': 'node',
            'name': name,
            'duration': duration,
            'description': description,
            'total_price': total_price,
            'vol': vol,
            'uom': uom
        }
    return None

@app.route('/api/parse-excel', methods=['POST'])
def parse_excel():
    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"}), 400

    file = request.files['file']
    try:
        wb = openpyxl.load_workbook(io.BytesIO(file.read()))
        sheet = wb.active

        hierarchy = []
        current_activity = None
        current_sub = None

        for row in sheet.iter_rows(min_row=2, values_only=True):  
            parsed = parse_row(row)
            print(parsed)
            if not parsed:
                continue

            if parsed['type'] == 'activity':
                current_activity = {
                    **parsed,
                    'sub_activities': []
                }
                hierarchy.append(current_activity)
                current_sub = None
            elif parsed['type'] == 'sub_activity':
                current_sub = {
                    **parsed,
                    'nodes': []
                }
                if current_activity:
                    current_activity['sub_activities'].append(current_sub)
            elif parsed['type'] == 'node' and current_sub:
                current_sub['nodes'].append(parsed)

        return jsonify({"data": hierarchy}), 200

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(port=5005, debug=True)
