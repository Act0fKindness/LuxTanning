from typing import Dict, List

from .kb_loader import load_knowledge, tokenize


class Retriever:
    def __init__(self) -> None:
        self.documents = load_knowledge()

    def search(self, query: str, limit: int = 5) -> List[Dict[str, str]]:
        query_tokens = set(tokenize(query))
        scored: List[Dict[str, str]] = []
        for doc in self.documents:
            overlap = len(query_tokens.intersection(doc["tokens"]))
            score = overlap / max(len(doc["tokens"]), 1)
            if overlap == 0:
                continue
            scored.append({"score": score, **doc})
        scored.sort(key=lambda item: item["score"], reverse=True)
        return scored[:limit]
