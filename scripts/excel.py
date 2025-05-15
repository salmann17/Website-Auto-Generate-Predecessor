from flask import Flask, request, jsonify
from flask_cors import CORS
import openpyxl
import re
import io

app = Flask(__name__)
CORS(app)

def parse_hierarchy(row):
    activity = str(row[0]).strip() if row[0] else ''
    duration = row[1] if len(row) > 1 else 0
    description = row[2] if len(row) > 2 else ''
    total_price = row[3] if len(row) > 3 else 0  

    if re.match(r'^\d+\.\s', activity):
        return {
            'type': 'activity',
            'name': re.sub(r'^\d+\.\s', '', activity)
        }
    elif re.match(r'^\*\s', activity):
        return {
            'type': 'sub_activity',
            'name': re.sub(r'^\*\s', '', activity)
        }
    elif re.match(r'^-\s', activity):
        return {
            'type': 'node',
            'name': re.sub(r'^-\s', '', activity),
            'duration': duration,
            'description': description,
            'total_price': total_price  
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
            parsed = parse_hierarchy(row)
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