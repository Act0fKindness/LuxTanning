from __future__ import annotations

import json
import logging
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List

from .config import get_settings
from .gemini_client import GeminiClient
from .retriever import Retriever
from .schemas import ChatRequest, ChatResponse


logger = logging.getLogger(__name__)

SYSTEM_INSTRUCTIONS = (
    "You are a tanning shop support assistant for {shop_name} in {shop_town}. "
    "Use only the provided knowledge base snippets and safe defaults when answering. "
    "Never invent medical claims, prices, or availability. "
    "If information is missing, ask a clarifying follow up question instead of guessing. "
    "Always remind sensitive-skin questions to patch test or consult a professional. "
    "Respond strictly as valid JSON matching the response schema."
)

RESPONSE_SCHEMA = json.dumps(
    {
        "answer": "string",
        "follow_up_question": "string or null",
        "actions": [
            {"type": "string", "label": "string", "url": "string optional"}
        ],
        "sources": [
            {"doc": "string", "chunk_id": "string"}
        ],
        "safety_notes": "string or null",
    },
    indent=2,
)


class ChatService:
    def __init__(self) -> None:
        self.settings = get_settings()
        self.retriever = Retriever()
        self.client = GeminiClient()
        self.log_file: Path = self.settings.log_file

    async def handle(self, request: ChatRequest) -> ChatResponse:
        snippets = self.retriever.search(request.message)
        prompt = self._build_prompt(request, snippets)
        payload = self._build_payload(prompt, snippets)
        try:
            raw = await self.client.generate(payload)
            parsed = self.client.parse_json_text(raw)
        except Exception as exc:
            logger.exception("Gemini generation failed: %s", exc)
            parsed = {}
        if not parsed:
            parsed = self._fallback_payload()
        response = ChatResponse(**parsed)
        if not response.sources and snippets:
            response.sources = [
                {"doc": snip["source"], "chunk_id": snip["chunk_id"]}
                for snip in snippets
            ]
        self._log_exchange(request, response)
        return response

    def _build_prompt(self, request: ChatRequest, snippets: List[Dict[str, Any]]) -> str:
        kb_lines = []
        for idx, snip in enumerate(snippets, start=1):
            kb_lines.append(
                f"Snippet {idx} ({snip['source']} - {snip['chunk_id']}): {snip['content']}"
            )
        kb_text = "\n".join(kb_lines) or "No knowledge snippets available."
        context = request.customer_context or "No additional customer context."
        return (
            f"Customer message: {request.message}\n"
            f"Customer context: {context}\n"
            f"Knowledge base:\n{kb_text}\n"
            f"Respond as {self.settings.bot_name}."
        )

    def _build_payload(self, prompt: str, snippets: List[Dict[str, Any]]) -> Dict[str, Any]:
        system_message = SYSTEM_INSTRUCTIONS.format(
            shop_name=self.settings.shop_name,
            shop_town=self.settings.shop_town,
        )
        return {
            "systemInstruction": {
                "role": "system",
                "parts": [{"text": f"{system_message}\nResponse schema:\n{RESPONSE_SCHEMA}"}],
            },
            "contents": [
                {
                    "role": "user",
                    "parts": [
                        {
                            "text": (
                                f"{prompt}\nIf no snippet applies, ask a clarifying question before acting."
                            )
                        }
                    ],
                }
            ],
            "generationConfig": {
                "response_mime_type": "application/json",
                "temperature": 0.3,
            },
        }

    def _log_exchange(self, request: ChatRequest, response: ChatResponse) -> None:
        payload = {
            "ts": datetime.utcnow().isoformat(),
            "session_id": request.session_id,
            "message": request.message,
            "response": response.dict(),
        }
        with self.log_file.open("a", encoding="utf-8") as log_file:
            log_file.write(json.dumps(payload) + "\n")

    @staticmethod
    def _fallback_payload() -> Dict[str, Any]:
        return {
            "answer": "I'm having trouble responding right now. Please try again in a moment.",
            "follow_up_question": None,
            "actions": [],
            "sources": [],
            "safety_notes": None,
        }
