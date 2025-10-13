import subprocess
import re
import json

def extract_diseases(pdf_path):
    print(f"Extracting text from {pdf_path}...")
    try:
        # Extract first 300 pages
        result = subprocess.run(['pdftotext', '-l', '300', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        text = result.stdout
        
        lines = text.split('\n')
        diseases = set()
        
        for line in lines:
            line_upper = line.upper()
            if any(keyword in line_upper for keyword in ["MALADIE ", "SYNDROME ", "INFECTION ", "INTOXICATION ", "PALUDISME", "CHOLERA", "TUBERCULOSE", "PNEUMONIE", "MENINGITE", "DIABETE", "HYPERTENSION"]):
                clean_line = line.strip()
                # filter out noisy lines
                if 5 < len(clean_line) < 100 and not clean_line.startswith('Tableau') and "..." not in clean_line and "Page" not in clean_line:
                    diseases.add(clean_line)
                    
        return list(diseases)
    except Exception as e:
        print(f"Error: {e}")
        return []

def main():
    pdfs = [
        "public/F540396569_CMR-96677.pdf",
        "public/guideline-339-fr (1).pdf",
        "public/Directives_de_traitement_des_patholigies_infectieuses.pdf"
    ]
    
    all_diseases = set()
    
    for pdf in pdfs:
        dis = extract_diseases(pdf)
        for d in dis:
            all_diseases.add(d)
            
    print(f"Found {len(all_diseases)} potential diseases.")
    
    # Save to JSON
    with open('pathologies_data.json', 'w', encoding='utf-8') as f:
        json.dump(list(all_diseases), f, ensure_ascii=False, indent=4)
        
    print("Saved to pathologies_data.json")

if __name__ == "__main__":
    main()
