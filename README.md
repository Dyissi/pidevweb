# 🏅 SPIN – Sports Performance Intelligence Network

**Revolutionizing university sports management with AI.**

SPIN is a Symfony 6.4 web/desktop application built by **Fullstack Attack** to streamline university sports club operations. It integrates AI-driven analytics, role-based dashboards (coaches, athletes, medical staff), and supports UN Sustainable Development Goals ❤️🎓⚙️🤝.

---

## 🚀 Features

- 🧠 AI-powered performance analytics via Python microservices  
- 🔒 Secure JWT-based RESTful API for multi-platform integration  
- 🧩 Modular Symfony bundles  
- 📅 Smart scheduling with calendar automation  
- 🧑‍💻 Dashboards tailored for coaches, athletes & medical staff  
- 🌍 SDG-aligned: Health (3), Education (4), Innovation (9), Partnerships (17)

---

## 🛠 Tech Stack

- **Backend:** Symfony 6.4, PHP 8.2  
- **Frontend:** Twig, Stimulus, Bootstrap  
- **Database:** MySQL  
- **Authentication:** JWT (LexikJWTAuthenticationBundle)  
- **AI Integration:** Python microservices, DeepAI, Gemini API  
- **PDF & Charts:** DomPDF (Nucleos), Google Charts (CMEN)  
- **Calendar & Scheduling:** tattali/calendar-bundle  
- **Security & Validation:** Google ReCAPTCHA (EWZ), InfoBip, Twilio  
- **Real-Time & Notifications:** Mercure, Messenger (Doctrine transport)  
- **Extras:** VichUploader, Geocoder, Paginator, Serializer (JMS), UX Components , OpenStreetMap
  
- ### 🔌 Integrated APIs
- 🤖 **DeepAI** – Image & text processing  
- 🔐 **LexikJWT** – JWT authentication  
- 🌐 **Google ReCAPTCHA** – Spam protection  
- 🗓️ **Google Calendar API** – Calendar sync  
- ☎️ **Twilio / InfoBip** – SMS notifications  
- 📢 **Mercure** – Real-time updates  
- 🧠 **Gemini API** – AI features  
- 🔊 **ElevenLabs / AssemblyAI** – Voice & audio processing  
- 🧮 **Workout API (API Ninjas)** – Fitness analytics  
- 🤗 **HuggingFace** – (Planned integration for NLP/ML)


---

## ⚙️ Installation

```bash
git clone https://github.com/Dyissi/pidevweb
cd pidevweb

composer install

cp .env .env.local
# Update DATABASE_URL in .env.local with your MySQL credentials

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# (Optional) Load sample data
php bin/console doctrine:fixtures:load
