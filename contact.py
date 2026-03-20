from flask import Flask, render_template, request, redirect, url_for, flash
import smtplib
from email.message import EmailMessage

app = Flask(__name__)
app.secret_key = "supersecretkey123"  # Needed for flash messages

# Certificates for portfolio
certificates = [
    {
        "title": "Udacity: AI Fundamentals",
        "link": "https://www.udacity.com/certificate/lp/36851d8e-b350-4fc3-aa70-e50e5246d29b"
    },
    {
        "title": "Udacity: AI Programming with Python Nanodegree",
        "link": "https://www.udacity.com/certificate/e/5fc0f1d2-2170-11f1-91ed-df267d5daba5"
    }
]

# Contact email credentials (hidden)
RECEIVER_EMAIL = "kassanewbelay@gmail.com"   # Your email to receive messages
SENDER_EMAIL = "your-sender-email@gmail.com" # Email used to send messages
SENDER_PASSWORD = "your-app-password"        # Gmail App Password


@app.route("/", methods=["GET", "POST"])
def home():
    if request.method == "POST":
        # Get form data
        name = request.form.get("name")
        email = request.form.get("email")
        message = request.form.get("message")

        # Prepare email
        msg = EmailMessage()
        msg["Subject"] = f"Portfolio Contact Form Message from {name}"
        msg["From"] = SENDER_EMAIL
        msg["To"] = RECEIVER_EMAIL
        msg.set_content(f"Name: {name}\nEmail: {email}\nMessage:\n{message}")

        # Send via Gmail SMTP
        try:
            with smtplib.SMTP_SSL("smtp.gmail.com", 465) as smtp:
                smtp.login(SENDER_EMAIL, SENDER_PASSWORD)
                smtp.send_message(msg)
            flash("✅ Your message has been successfully submitted!", "success")
        except Exception as e:
            print("Error sending email:", e)
            flash("❌ Sorry, something went wrong. Please try again later.", "error")

        return redirect(url_for("home"))

    return render_template("index.html", certificates=certificates)


if __name__ == "__main__":
    app.run(debug=True)
