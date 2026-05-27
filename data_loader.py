import pandas as pd
import pdfplumber
import os

STUDENTS_DIR = "students"

def load_excel_data():
    students = {}
    for file in os.listdir(STUDENTS_DIR):
        if file.endswith(".xlsx") or file.endswith(".xls"):
            path = os.path.join(STUDENTS_DIR, file)
            df = pd.read_excel(path)
            # نحول كل صف لقاموس
            for _, row in df.iterrows():
                # غير "الاسم الكامل" حسب اسم العمود عندك
                name = str(row.get("الاسم الكامل", "")).strip()
                if name:
                    students[name] = row.to_dict()
    return students

def load_pdf_data():
    texts = {}
    for file in os.listdir(STUDENTS_DIR):
        if file.endswith(".pdf"):
            path = os.path.join(STUDENTS_DIR, file)
            with pdfplumber.open(path) as pdf:
                text = ""
                for page in pdf.pages:
                    t = page.extract_text()
                    if t:
                        text += t + "\n"
            name = file.replace(".pdf", "").strip()
            texts[name] = text
    return texts

def search_student(query, excel_data, pdf_data):
    results = []
    query = query.strip().lower()

    # البحث في Excel
    for name, data in excel_data.items():
        if query in name.lower():
            info = "\n".join([
                f"**{k}**: {v}"
                for k, v in data.items()
                if str(v).strip() and str(v) != "nan"
            ])
            results.append(f"📋 *{name}*\n{info}")

    # البحث في PDF
    for name, text in pdf_data.items():
        if query in name.lower() or query in text.lower():
            preview = text[:300]
            results.append(f"📄 *{name}*\n{preview}")

    return results
