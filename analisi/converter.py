import openpyxl
import json
import os

def excel_to_json(file_path, output_json):
    if not os.path.exists(file_path):
        print(f"Error: {file_path} not found.")
        return

    wb = openpyxl.load_workbook(file_path, data_only=True)
    sheet = wb.active # Asumimos que la data está en la primera hoja activa
    
    data = []
    rows = list(sheet.rows)
    if not rows:
        return

    headers = [str(cell.value).strip() if cell.value else "" for cell in rows[0]]
    
    for row in rows[1:]:
        row_data = {}
        for index, cell in enumerate(row):
            header = headers[index]
            if header:
                val = cell.value
                # Convertir fechas a string para JSON
                if hasattr(val, 'isoformat'):
                    val = val.isoformat()
                row_data[header] = val
        
        # Solo agregar si tiene al menos un campo clave (como Ingreso)
        if row_data.get('Ingreso'):
            data.append(row_data)

    with open(output_json, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
    
    print(f"Success: {len(data)} rows exported to {output_json}")

if __name__ == "__main__":
    excel_to_json("consulta enero a marzo.xlsx", "data_import.json")
