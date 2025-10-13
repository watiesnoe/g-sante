import os
import re
import json
import subprocess

def extract_from_pdf(pdf_path):
    print(f"Extracting text from {pdf_path}...")
    try:
        # Run pdftotext
        result = subprocess.run(['pdftotext', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        return result.stdout
    except Exception as e:
        print(f"Error extracting {pdf_path}: {e}")
        return ""

def parse_medicines(text):
    medicines = set()
    # Simple regex to find lines that look like medications: 
    # Starts with an uppercase letter, contains mg, g, ml, UI, comprimé, etc.
    # Ex: "Amoxicilline, gélule 500 mg"
    pattern = re.compile(r'^([A-Z][a-zA-Z0-9\s\,\-\+\/]+(?:mg|g|ml|UI|comprimé|gélule|sirop|ampoule|flacon|suspension)).*', re.MULTILINE)
    
    matches = pattern.findall(text)
    for match in matches:
        clean_name = match.strip()
        # Filter out obvious false positives (too long, no spaces, etc.)
        if 5 < len(clean_name) < 100 and not "CHAPITRE" in clean_name.upper():
            medicines.add(clean_name)
    
    return list(medicines)

def main():
    pdfs = [
        "public/guideline-339-fr (1).pdf",
        "public/liste-nationale-des-medicaments-essentiels-2020_0.pdf"
    ]
    
    all_medicines = set()
    
    for pdf in pdfs:
        text = extract_from_pdf(pdf)
        meds = parse_medicines(text)
        for m in meds:
            all_medicines.add(m)
            
    print(f"Found {len(all_medicines)} unique medications from PDFs.")
    
    # Save to JSON
    output_data = [{"nom": m} for m in all_medicines]
    
    with open('medicines_data.json', 'w', encoding='utf-8') as f:
        json.dump(output_data, f, ensure_ascii=False, indent=4)
        
    print("Saved to medicines_data.json")

if __name__ == "__main__":
    main()
