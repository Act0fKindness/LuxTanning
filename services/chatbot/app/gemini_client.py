from __future__ import annotations

import json
import logging
from typing import Any, Dict

import httpx

from .config import get_settings


logger = logging.getLogger(__name__)


class GeminiClient:
    def __init__(self) -> None:
        self.settings = get_settings()
        self.base_url = "https://generativelanguage.googleapis.com/v1beta"
        self.model = self._normalise_model(self.settings.gemini_model)

    async def generate(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        url = f"{self.base_url}/models/{self.model}:generateContent"
        headers = {
            "x-goog-api-key": self.settings.gemini_api_key,
            "Content-Type": "application/json",
        }
        async with httpx.AsyncClient(timeout=30) as client:
            last_error: Exception | None = None
            for attempt in range(2):
                try:
                    response = await client.post(url, headers=headers, json=payload)
                    response.raise_for_status()
                    return response.json()
                except httpx.HTTPError as exc:  # pragma: no cover - network dependent
                    last_error = exc
                    logger.warning("Gemini request failed (attempt %s): %s", attempt + 1, exc)
            raise RuntimeError(f"Gemini API error: {last_error}")

    @staticmethod
    def parse_json_text(raw_response: Dict[str, Any]) -> Dict[str, Any]:
        candidates = raw_response.get("candidates", [])
        if not candidates:
            return {}
        content = candidates[0].get("content", {})
        parts = content.get("parts", [])
        if not parts:
            return {}
        try:
            return json.loads(parts[0].get("text", "{}"))
        except json.JSONDecodeError:
            return {}

    @staticmethod
    def _normalise_model(model: str) -> str:
        value = (model or "").strip()
        if not value:
            raise ValueError("GEMINI_MODEL must be configured")
        prefix = "models/"
        if value.startswith(prefix):
            return value[len(prefix):]
        return value
