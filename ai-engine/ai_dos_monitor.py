import requests
import json
import time
from collections import Counter
from datetime import datetime, timedelta

LOG_FILE = "/var/log/nginx/access.log"
DECISION_LOG = "/var/log/nginx_ai_decision.log"
STATE_FILE = "/var/run/nginx_dos.state"

OLLAMA_URL = "http://localhost:11434/api/generate"
MODEL_NAME = "mistral"

INTERVAL = 15


def read_logs():

    try:
        with open(LOG_FILE, "r") as f:
            lines = f.readlines()

        now = datetime.now()
        one_minute_ago = now - timedelta(minutes=1)

        filtered = []

        for line in reversed(lines):

            try:
                timestamp = line.split("[")[1].split("]")[0]
                log_time = datetime.strptime(
                    timestamp.split()[0],
                    "%d/%b/%Y:%H:%M:%S"
                )

                if log_time >= one_minute_ago:
                    filtered.append(line)
                else:
                    break

            except:
                continue

        filtered.reverse()

        return filtered

    except:
        return []


def analyze_logs(lines):

    ip_counter = Counter()
    endpoint_counter = Counter()

    total_requests = 0
    error_count = 0
    timestamps = []

    for line in lines:

        parts = line.split()

        if len(parts) < 9:
            continue

        ip = parts[0]
        endpoint = parts[6]
        status = parts[8]

        total_requests += 1
        ip_counter[ip] += 1
        endpoint_counter[endpoint] += 1

        if status.startswith("4") or status.startswith("5"):
            error_count += 1

        try:
            timestamp = line.split("[")[1].split("]")[0]
            log_time = datetime.strptime(
                timestamp.split()[0],
                "%d/%b/%Y:%H:%M:%S"
            )
            timestamps.append(log_time)
        except:
            pass

    top_ips = ip_counter.most_common(5)
    top_endpoints = endpoint_counter.most_common(5)

    error_percent = 0
    if total_requests > 0:
        error_percent = (error_count / total_requests) * 100

    top_ip_ratio = 0
    attacker_ip = None
    attacker_rps = 0

    if top_ips:
        attacker_ip = top_ips[0][0]
        top_ip_ratio = (top_ips[0][1] / total_requests) * 100

    rps = 0

    if len(timestamps) > 1:
        duration = (max(timestamps) - min(timestamps)).total_seconds()
        if duration > 0:
            rps = total_requests / duration

            if attacker_ip:
                attacker_rps = ip_counter[attacker_ip] / duration

    return {
        "total_requests": total_requests,
        "top_ips": top_ips,
        "top_endpoints": top_endpoints,
        "error_percent": error_percent,
        "top_ip_ratio": top_ip_ratio,
        "requests_per_second": rps,
        "attacker_ip": attacker_ip,
        "attacker_rps": attacker_rps
    }


def build_prompt(stats):

    prompt = f"""
NGINX Traffic Analysis

Total Requests (last 1 minute): {stats['total_requests']}
Requests Per Second: {stats['requests_per_second']:.2f}

Top IPs: {stats['top_ips']}
Top Endpoints: {stats['top_endpoints']}

Top Attacker IP: {stats['attacker_ip']}
Attacker Requests Per Second: {stats['attacker_rps']:.2f}

Error Percentage: {stats['error_percent']:.2f}%
Top IP Traffic Share: {stats['top_ip_ratio']:.2f}%

Determine if this traffic represents a Denial of Service attack.

Respond with exactly one word:

DOS
or
NORMAL
"""

    return prompt


def ask_mistral(prompt):

    start = time.perf_counter()

    response = requests.post(
        OLLAMA_URL,
        json={
            "model": MODEL_NAME,
            "prompt": prompt,
            "stream": False
        }
    )

    end = time.perf_counter()

    duration_ms = (end - start) * 1000

    result = response.json()

    tokens = result.get("eval_count", 0)

    return result, duration_ms, tokens


def extract_decision(response):

    text = response.get("response", "").upper()

    if "DOS" in text:
        return "DOS"

    if "NORMAL" in text:
        return "NORMAL"

    return "UNKNOWN"


def update_protection_state(decision):

    if decision == "DOS":
        new_state = "on"
    elif decision == "NORMAL":
        new_state = "off"
    else:
        return None

    try:
        with open(STATE_FILE, "r") as f:
            current = f.read().strip()
    except:
        current = "off"

    if current != new_state:

        with open(STATE_FILE, "w") as f:
            f.write(new_state)

        return new_state

    return current


def write_log(stats, decision, protection_state):

    timestamp = time.strftime("%Y-%m-%d %H:%M:%S")

    log_text = f"""
{timestamp}

Total Requests (1 min): {stats['total_requests']}
Requests Per Second: {stats['requests_per_second']:.2f}

Top IPs: {stats['top_ips']}
Top Endpoints: {stats['top_endpoints']}

Top Attacker IP: {stats['attacker_ip']}
Attacker RPS: {stats['attacker_rps']:.2f}

Top IP Share: {stats['top_ip_ratio']:.2f}%
Error Percentage: {stats['error_percent']:.2f}%

AI Decision: {decision}
Protection State: {protection_state}

--------------------------------------------------
"""

    with open(DECISION_LOG, "a") as f:
        f.write(log_text)


def main():

    print("\nAI DoS Monitor Running")

    lines = read_logs()

    if not lines:
        print("No recent logs")
        return

    stats = analyze_logs(lines)

    prompt = build_prompt(stats)

    print("\nPrompt sent to Mistral:\n")
    print(prompt)

    response, duration_ms, tokens = ask_mistral(prompt)

    print("\nRaw LLM Response:\n")
    print(json.dumps(response, indent=2))

    decision = extract_decision(response)

    protection_state = update_protection_state(decision)

    write_log(stats, decision, protection_state)

    print("\nDecision:", decision)
    print("Protection:", protection_state)
    print("Tokens:", tokens)
    print("LLM Response Time:", round(duration_ms, 2), "ms")


if __name__ == "__main__":

    while True:

        try:
            main()
        except Exception as e:
            print("Runtime error:", e)

        time.sleep(INTERVAL)
