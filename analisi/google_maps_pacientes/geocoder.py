import json
import requests
import os
import mysql.connector
from dotenv import load_dotenv

# 🛰️ EL REY DEL MAPA: GEOCODER REAL-TIME (v3.0)
# Carga las credenciales automágicamente desde .env
load_dotenv()

API_KEY = "AIzaSyD7MSyr5kNxMPllhBGIwxA3tjG6N73e9os"
REGION_BIAS = ", Norte de Santander, Colombia"
URL_GEO = "https://maps.googleapis.com/maps/api/geocode/json"

def get_db_connection():
    try:
        return mysql.connector.connect(
            host=os.getenv("DB_HOST", "127.0.0.1"),
            user=os.getenv("DB_USERNAME", "root"),
            password=os.getenv("DB_PASSWORD", "root"),
            database=os.getenv("DB_DATABASE", "app_domiciliaria"),
            port=os.getenv("DB_PORT", 3306)
        )
    except Exception as e:
        print(f"❌ Error de conexión DB: {e}")
        return None

def process_geocoding():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    input_file = os.path.join(base_dir, "pendientes.json")
    
    if not os.path.exists(input_file):
        print(f"❌ Error: No se encontró '{input_file}'")
        return

    # Conexión Real-Time
    conn = get_db_connection()
    if not conn: return
    cursor = conn.cursor()

    with open(input_file, 'r', encoding='utf-8') as f:
        pacientes = json.load(f)

    total = len(pacientes)
    print(f"🛰️  GEOCODER REAL-TIME INICIADO. Procesando {total} pacientes...")

    count = 0
    errors = 0

    for p in pacientes:
        # Verificar si ya tiene latitud en DB (por si pausamos y reiniciamos)
        cursor.execute("SELECT latitud FROM pacientes WHERE id_paciente = %s", (p['id_paciente'],))
        db_p = cursor.fetchone()
        if db_p and db_p[0] is not None and float(db_p[0]) != 0:
            continue

        direccion_completa = f"{p['direccion']}{REGION_BIAS}"
        
        try:
            params = {"address": direccion_completa, "key": API_KEY, "language": "es"}
            resp = requests.get(URL_GEO, params=params).json()

            if resp['status'] == 'OK':
                loc = resp['results'][0]['geometry']['location']
                lat, lng = loc['lat'], loc['lng']
                url_maps = f"https://www.google.com/maps?q={lat},{lng}"
                
                # ACTUALIZACIÓN EN TIEMPO REAL ⚡
                sql = """UPDATE pacientes 
                         SET latitud = %s, longitud = %s, url_google_maps = %s, updated_at = NOW() 
                         WHERE id_paciente = %s"""
                cursor.execute(sql, (lat, lng, url_maps, p['id_paciente']))
                conn.commit()
                
                print(f"✅ [{count+1}/{total}] DB UPDATED: {p['nombre_completo']}")
            elif resp['status'] == 'OVER_QUERY_LIMIT':
                print("🛑 Límite de Google excedido. Deteniendo.")
                break
            else:
                print(f"⚠️ [{count+1}/{total}] No encontrado: {p['nombre_completo']} ({resp['status']})")
                errors += 1
                
            count += 1

        except Exception as e:
            print(f"❌ Error en registro {p['id_paciente']}: {e}")
            errors += 1

    conn.close()
    print(f"\n🏁 PROCESO TERMINADO.")
    print(f"📦 Procesados: {count} | ⚠️ Fallidos/No encontrados: {errors}")
    print("🚀 ¡Tus pacientes ya están cargados en el mapa en tiempo real!")

if __name__ == "__main__":
    process_geocoding()
