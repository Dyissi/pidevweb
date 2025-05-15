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
- **APIs:** JWT Auth, AI via Python microservices

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
