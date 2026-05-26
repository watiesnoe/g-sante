import subprocess
import re

def analyze_pdf(pdf_path, name):
    print(f"--- Analyzing {name} ---")
    try:
        # Extract first 50 pages just to get the Table of Contents or initial diseases
        result = subprocess.run(['pdftotext', '-l', '50', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        text = result.stdout
        
        # Look for words like "Maladie", "Traitement", "Syndrome", "Infection"
        lines = text.split('\n')
        diseases = []
        for line in lines:
            line_upper = line.upper()
            if "MALADIE" in line_upper or "SYNDROME" in line_upper or "INFECTION" in line_upper or "INTOXICATION" in line_upper:
                if len(line.strip()) > 5 and len(line.strip()) < 80:
                    diseases.append(line.strip())
        
        # Print a sample
        print(f"Found {len(diseases)} potential disease headers.")
        for d in diseases[:15]:
            print(" - " + d)
        print("\n")
            
    except Exception as e:
        print(f"Error: {e}")

analyze_pdf('public/F540396569_CMR-96677.pdf', 'Cameroun Maladies Pro')
analyze_pdf('public/guideline-339-fr (1).pdf', 'MSF Guidelines')
