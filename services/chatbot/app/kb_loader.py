from pathlib import Path
from typing import Dict, List
import json
import re

from .config import get_settings

TOKEN_PATTERN = re.compile(r"[a-z0-9']+")


def tokenize(text: str) -> List[str]:
    return TOKEN_PATTERN.findall(text.lower())


def chunk_markdown(path: Path) -> List[Dict[str, str]]:
    raw = path.read_text(encoding="utf-8")
    sections = [part.strip() for part in raw.split("\n\n") if part.strip()]
    chunks = []
    for idx, section in enumerate(sections, start=1):
        chunk_id = f"{path.name}-{idx}"
        chunks.append({
            "chunk_id": chunk_id,
            "source": path.name,
            "content": section,
            "tokens": tokenize(section),
        })
    return chunks


def load_pricing(path: Path) -> List[Dict[str, str]]:
    data = json.loads(path.read_text(encoding="utf-8"))
    chunks = []
    for idx, row in enumerate(data, start=1):
        content = f"{row['name']} costs {row['price']}. {row['details']}"
        chunks.append({
            "chunk_id": f"{path.name}-{idx}",
            "source": path.name,
            "content": content,
            "tokens": tokenize(content),
        })
    return chunks


def load_knowledge() -> List[Dict[str, str]]:
    settings = get_settings()
    knowledge_dir = settings.knowledge_dir
    documents: List[Dict[str, str]] = []
    for md_file in knowledge_dir.glob("*.md"):
        documents.extend(chunk_markdown(md_file))
    pricing_file = knowledge_dir / "pricing.json"
    if pricing_file.exists():
        documents.extend(load_pricing(pricing_file))
    return documents
