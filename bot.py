import os
import logging
import threading
from http.server import HTTPServer, BaseHTTPRequestHandler
from telegram import Update
from telegram.ext import ApplicationBuilder, CommandHandler, MessageHandler, filters, ContextTypes
from data_loader import load_excel_data, load_pdf_data, search_student
from dotenv import load_dotenv

load_dotenv()
TOKEN = os.getenv("BOT_TOKEN")

logging.basicConfig(level=logging.INFO)

excel_data = load_excel_data()
pdf_data = load_pdf_data()

# ===== Web Server بسيط لـ Render =====
class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b"Bot is running!")
    def log_message(self, format, *args):
        pass  # نخفي logs الـ server

def run_server():
    port = int(os.getenv("PORT", 8080))
    server = HTTPServer(("0.0.0.0", port), Handler)
    server.serve_forever()

# ===== البوت =====
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(
        "👋 أهلاً! أرسل اسم الطالب وسأبحث عنه في السجلات."
    )

async def handle_message(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.message.text
    results = search_student(query, excel_data, pdf_data)

    if results:
        for r in results[:3]:
            await update.message.reply_text(r, parse_mode="Markdown")
    else:
        await update.message.reply_text("❌ ما لقيت أي طالب بهذا الاسم.")

def main():
    # شغّل الـ server في thread منفصل
    thread = threading.Thread(target=run_server)
    thread.daemon = True
    thread.start()

    app = ApplicationBuilder().token(TOKEN).build()
    app.add_handler(CommandHandler("start", start))
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_message))
    app.run_polling()

if __name__ == "__main__":
    main()
