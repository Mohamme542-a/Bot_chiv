import os
import logging
from telegram import Update
from telegram.ext import ApplicationBuilder, CommandHandler, MessageHandler, filters, ContextTypes
from data_loader import load_excel_data, load_pdf_data, search_student
from dotenv import load_dotenv

load_dotenv()
TOKEN = os.getenv("8831658490:AAHEEiBzy4uEHDz7oVAQmRzJfHDNkNGjYYQ")

logging.basicConfig(level=logging.INFO)

# تحميل البيانات عند بدء البوت
excel_data = load_excel_data()
pdf_data = load_pdf_data()

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(
        "👋 أهلاً! أرسل اسم الطالب وسأبحث عنه في السجلات."
    )

async def handle_message(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.message.text
    results = search_student(query, excel_data, pdf_data)

    if results:
        for r in results[:3]:  # أول 3 نتائج
            await update.message.reply_text(r, parse_mode="Markdown")
    else:
        await update.message.reply_text("❌ ما لقيت أي طالب بهذا الاسم.")

def main():
    app = ApplicationBuilder().token(TOKEN).build()
    app.add_handler(CommandHandler("start", start))
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_message))
    app.run_polling()

if __name__ == "__main__":
    main()
