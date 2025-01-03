import psycopg2
from psycopg2 import sql
import os

# Superbase konfiguratsiyasi
SUPERBASE_API_URL = 'https://kbzxwaolakuykdbhfmuz.supabase.co'  # Supabase API endpoint
SUPERBASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imtienh3YW9sYWt1eWtkYmhmbXV6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzU5MjI3MzMsImV4cCI6MjA1MTQ5ODczM30.jnRurg0Tuaeax2pi3rXdwOjTeqbhehBm9FSdRq0YqP8'
SUPERBASE_SERVICE_ROLE_SECRET = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imtienh3YW9sYWt1eWtkYmhmbXV6Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczNTkyMjczMywiZXhwIjoyMDUxNDk4NzMzfQ.WDJAhqH8Z5uQJtvt2ckocwmOPR_GUOeqI7xs0JgdLvQ'

# DB ulanishini amalga oshirish
def connect_to_superbase():
    try:
        connection = psycopg2.connect(
            dsn=SUPERBASE_API_URL,
            user="postgres",
            password=SUPERBASE_ANON_KEY  # yoki SUPERBASE_SERVICE_ROLE_SECRET foydalanuvchi kerak bo'lsa
        )
        return connection
    except Exception as e:
        print(f"Error: {e}")
        return None

# Ulanishni yopish
def close_connection(connection):
    if connection:
        connection.close()

# Misol uchun ulanish
conn = connect_to_superbase()
if conn:
    print("Superbase-ga ulanish muvaffaqiyatli!")
    close_connection(conn)
