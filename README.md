# 🛡️ SentinelSphere: AI-Driven DoS Detection and Mitigation in Cloud

SentinelSphere is an AI-powered cloud security framework developed to detect and mitigate **Denial-of-Service (DoS)** attacks in real time. The framework combines **Large Language Models (LLMs)**, network traffic analytics, automated mitigation, and real-time monitoring to provide intelligent protection for cloud-based environments.

The system continuously analyzes NGINX traffic, classifies incoming requests using the **Mistral Large Language Model (LLM)** running locally through **Ollama**, and automatically enables or disables mitigation mechanisms based on the detected threat level.

> **Disclaimer**
>
> This project was developed for **educational and research purposes** as part of a B.Tech Cyber Security academic project. All configuration files, screenshots, documentation, IP addresses, hostnames, domains, and credentials included in this repository have been sanitized to remove sensitive production information.

---

# 📌 Table of Contents

- Overview
- Features
- Technology Stack
- System Architecture
- Repository Structure
- Workflow
- Installation
- Project Components
- Experimental Results
- Documentation
- Future Enhancements
- Contributors
- License
- Acknowledgements

---

# 🚀 Overview

SentinelSphere provides an intelligent approach to cloud security by combining traditional network monitoring with AI-assisted decision making.

The framework performs the following tasks:

- Collects NGINX access logs
- Extracts traffic statistics
- Builds an AI prompt
- Uses the Mistral LLM to classify traffic
- Determines whether traffic is **NORMAL** or **DOS**
- Automatically activates mitigation when attacks are detected
- Displays system metrics through Prometheus and Grafana

---

# ✨ Features

- 🤖 AI-driven DoS attack detection using Mistral LLM
- 🧠 Local LLM inference through Ollama
- 🌐 Real-time NGINX traffic monitoring
- ⚡ ApacheBench DoS attack simulation
- 🛡️ Automated mitigation engine
- 📊 Prometheus metrics collection
- 📈 Grafana monitoring dashboards
- 🖥️ Web-based monitoring dashboard
- 🔄 Automatic protection state management
- ☁️ Cloud-oriented architecture

---

# 🛠 Technology Stack

| Category | Technology |
|----------|------------|
| Programming | Python, PHP, Bash |
| AI Model | Mistral |
| LLM Runtime | Ollama |
| Web Server | NGINX |
| Monitoring | Prometheus |
| Visualization | Grafana |
| Attack Simulation | ApacheBench |
| Operating System | Linux |

---

# 🏗 System Architecture

## Architecture Diagram

![Architecture](docs/architecture/architecture.jpeg)

---

## Block Diagram

![Block Diagram](docs/architecture/block_diagram.png)

---

## Flow Diagram

![Flow Diagram](docs/architecture/flow_diagram.png)

---

# 📂 Repository Structure

```text
SentinelSphere
│
├── ai-engine/
│
├── attacker-server/
│
├── target-server/
│
├── docs/
│   ├── architecture/
│   ├── paper/
│   └── results/
│
├── README.md
├── LICENSE
├── requirements.txt
└── .gitignore
```

---

# 🔄 Workflow

1. ApacheBench generates client requests.
2. NGINX records incoming traffic.
3. Python analyzes the latest access logs.
4. Traffic statistics are converted into an AI prompt.
5. The prompt is sent to the Mistral LLM using Ollama.
6. The AI classifies traffic as:
   - NORMAL
   - DOS
7. Protection is automatically enabled or disabled.
8. Prometheus collects metrics.
9. Grafana visualizes the system status.

---

# ⚙️ Installation

## Clone Repository

```bash
git clone https://github.com/AAMILAF/SentinelSphere-AI-Driven-DoS-Detection-and-Mitigation-in-Cloud.git
```

---

## Navigate to Project

```bash
cd SentinelSphere-AI-Driven-DoS-Detection-and-Mitigation-in-Cloud
```

---

## Install Python Dependencies

```bash
pip install -r requirements.txt
```

---

## Install Ollama

Download and install Ollama from:

https://ollama.com/

Pull the Mistral model:

```bash
ollama pull mistral
```

---

# 📦 Project Components

## AI Engine

- Reads NGINX access logs
- Extracts traffic statistics
- Generates AI prompts
- Queries the Mistral LLM
- Determines traffic classification

---

## ApacheBench

Generates simulated DoS traffic for testing and evaluation.

---

## NGINX

- Receives incoming traffic
- Stores access logs
- Works with the mitigation module

---

## Prometheus

Collects:

- CPU Usage
- Memory Usage
- Network Statistics
- System Metrics

---

## Grafana

Visualizes:

- CPU Usage
- Memory Usage
- Network Traffic
- Disk Usage
- System Health

---

## Web Dashboard

Provides:

- AI Decision Status
- Protection Status
- Attack Control
- Log Viewer

---

# 📊 Experimental Results

The repository contains experimental results demonstrating:

- Normal Traffic Detection
- DoS Detection
- Automatic Protection Activation
- ApacheBench Benchmark Results
- AI Decision Logs
- Monitoring Dashboards
- Performance Comparison

---

# 📷 Results

## AI Detection

![AI Detection](docs/results/result-10.png)

---

## AI Decision

![AI Decision](docs/results/result-11.png)

---

## Protection Dashboard

![Protection](docs/results/result-14.png)

---

## Grafana Dashboard

![Grafana](docs/results/result-20.png)

---

## ApacheBench Benchmark

![Benchmark](docs/results/result-15.png)

---

# 📚 Documentation

The repository includes:

- Architecture Diagram
- Block Diagram
- Flow Diagram
- AI Detection Results
- ApacheBench Results
- Grafana Dashboards
- Detection Screenshots
- Mitigation Screenshots
- Research Paper
- Project Presentation

---

# 🔮 Future Enhancements

- Kubernetes deployment
- Distributed DoS detection
- Explainable AI integration
- Multi-model ensemble detection
- Cloud-native scalability
- Multi-node monitoring
- Advanced anomaly detection

---

# 👥 Contributors

- **Aamila Fathima M**
- **Aathila Fathima M**

---

# 📄 License

This project is licensed under the **MIT License**.

See the **LICENSE** file for complete details.

---

# 🙏 Acknowledgements

This project was developed as part of the **Bachelor of Technology (B.Tech) in Computer Science and Engineering (Cyber Security)** curriculum for academic and research purposes.

Special thanks to the faculty members, project mentors, and the open-source community for providing the tools and technologies that supported the development of this project.
