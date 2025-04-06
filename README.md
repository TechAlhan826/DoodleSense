# 🎨 DoodleSense — Draw. Think. Recognize.

> Your AI-powered canvas that does more than just draw.

---

## 🚀 What’s This?

**DoodleSense** isn’t just another drawing board. It’s an intelligent, minimalist, yet powerful web app that lets users **sketch freely**, **save their art**, and then watch it get **decoded by AI** — yes, real-time visual recognition magic 🧠✨. Whether you're an artist, student, or just doodling for fun — this platform *sees* what you mean.

---

## 🧠 Core Features

- 🎨 **Interactive Drawing Canvas**  
  Built on Vanilla JS + Fabric.js (no React overhead), it’s smooth, responsive, and intuitive.

- 🔒 **Authentication System (Login/Signup/Forget Password)**  
  No nonsense — secure, session-based auth powered by PHP & MySQL. Mail handling? Handled via **PHPMailer SMTP**.

- 📬 **Email Verification & Recovery**  
  Yep, full-fledged mail flows — OTPs, reset links, the whole shebang.

- 🖼️ **AI-Powered Recognition**  
  Upload your doodle → we send it to a configured AI (like Gemini API) → BOOM 💥 it tells you what it sees.

- 🗃️ **Dashboard & History**  
  A user-friendly dashboard inspired by ChatGPT layouts — where your past creations chill until you need them.

- 🌐 **Home Page**  
  Modern landing page with CTA that doesn’t scream “college project” — it’s legit.

- 🧪 **Guest Mode + localStorage**  
  Not logged in? You won’t lose your sketch. We got it covered until you do log in.

---

## 🛠️ Tech Stack

| Layer        | Stack                                  |
| ------------ | -------------------------------------- |
| Frontend     | HTML, CSS (custom + minimal Tailwind), JavaScript, Fabric.js |
| Backend      | PHP 7.4 (raw, no frameworks), MySQL    |
| Auth & Mail  | PHP Sessions, PHPMailer (SMTP)         |
| AI API       | Gemini API / Vision model integration  |
| Hosting      | Netlify (frontend), custom hosting (backend) |

---

## 🧩 System Design

Want the internals?

- ✅ Auth & session handled in-page (no unnecessary redirections)
- ✅ Every module talks clean to MySQL
- ✅ AI hooked via PHP fetch + image blob transfer
- ✅ Guest sessions managed with localStorage
- ✅ Emails? SMTP magic via PHPMailer

> For full UML, ER Diagrams & data flows — check `/uml/` folder. (We don’t leave loose ends.)

---

## 📸 Screenshots

> _Add screenshots of: Home → Drawing Canvas → AI Output → Dashboard → Login flow_  
_You know the drill. Real project? Real visuals._

---

## 🧪 Setup & Run

```bash
# Clone it
git clone https://github.com/yourusername/DoodleSense.git
cd DoodleSense

# Set up backend
Import `database.sql` into your MySQL DB
Configure `/config/db.php` with your DB creds
Set your SMTP details in `/config/mail.php`

# Done. Run the app locally or push to your server.

