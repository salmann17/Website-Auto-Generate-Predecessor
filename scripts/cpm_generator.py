from flask import Flask, request, jsonify
from langchain_core.output_parsers import JsonOutputParser
from langchain_core.prompts import PromptTemplate
from langchain_core.pydantic_v1 import BaseModel, Field
from langchain_google_genai import ChatGoogleGenerativeAI
import sqlite3
from flask_cors import CORS
import pandas as pd  

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}}) 
class ActivityItem(BaseModel):
    ID: str = Field(description="ID of the Activity")
    Activity: str = Field(description="Name of the Activity")
    Duration: str = Field(description="Duration of the Activity")
    Predecessors: str = Field(description="Predecessors of the Activity")
class CPM(BaseModel):
    items: list = Field(description="List of activities")

parser = JsonOutputParser(pydantic_object=CPM)
llm = ChatGoogleGenerativeAI(
    model="gemini-1.5-flash",
    api_key="AIzaSyAnQXK-QcMWawunnxQ92kfELdT4NDzZIGc",
    temperature=0.3
)

prompt = PromptTemplate(
    template="""Buat tabel CPM untuk proyek berikut dengan kolom:
    - Activity (Nama Aktivitas)
    - Duration (Durasi)
    - Predecessors (Pendahulu)
    
    {format_instructions}
    
    Deskripsi Proyek:
    {query}""",
    input_variables=["query"],
    partial_variables={"format_instructions": parser.get_format_instructions()},
)

chain = prompt | llm | parser

@app.route('/process', methods=['POST'])
def process_prompt():
    try:
        data = request.get_json()
        query = data['prompt']
        
        results = chain.invoke({"query": query})
        
        df = pd.DataFrame(results['items'])
        
        return jsonify({
            'status': 'success',
            'data': df.to_dict(orient='records')
        })
        
    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5025)