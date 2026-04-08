from __future__ import annotations

from flask import Flask, jsonify, request

POSITIVE_WORDS = {
    "excellent",
    "great",
    "smooth",
    "luxury",
    "clean",
    "responsive",
    "professional",
    "amazing",
    "timely",
    "beautiful",
    "perfect",
    "helpful",
}

NEGATIVE_WORDS = {
    "bad",
    "late",
    "poor",
    "dirty",
    "noisy",
    "slow",
    "rude",
    "broken",
    "awful",
    "weak",
    "issue",
    "problem",
}

app = Flask(__name__)


def score_review(review: str) -> float:
    tokens = [token.strip(".,!?;:()[]{}\"'").lower() for token in review.split()]
    tokens = [token for token in tokens if token]

    if not tokens:
        return 0.5

    positive_hits = sum(token in POSITIVE_WORDS for token in tokens)
    negative_hits = sum(token in NEGATIVE_WORDS for token in tokens)
    raw_score = (positive_hits - negative_hits) / max(len(tokens), 1)
    normalized = 0.5 + raw_score * 2.5
    return round(min(1.0, max(0.0, normalized)), 3)


@app.post("/api/sentiment")
def analyze_sentiment():
    payload = request.get_json(silent=True) or {}
    reviews = payload.get("reviews", [])

    if not isinstance(reviews, list) or not all(isinstance(item, str) for item in reviews):
        return jsonify({"error": "Field 'reviews' must be a list of strings."}), 400

    scores = [{"review": review, "score": score_review(review)} for review in reviews]
    average_score = round(sum(item["score"] for item in scores) / len(scores), 3) if scores else 0.0

    return jsonify({
        "scores": scores,
        "average_score": average_score,
        "review_count": len(scores),
    })


@app.get("/health")
def healthcheck():
    return jsonify({"status": "ok"})
