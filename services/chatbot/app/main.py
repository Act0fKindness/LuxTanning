import logging

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from .chat_service import ChatService
from .schemas import ChatRequest, ChatResponse

logger = logging.getLogger(__name__)

app = FastAPI(title="Luma OS Chatbot")
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"]
)
chat_service = ChatService()


@app.get("/health")
async def health() -> dict:
    return {"status": "ok"}


@app.post("/chat", response_model=ChatResponse)
async def chat(payload: ChatRequest) -> ChatResponse:
    try:
        return await chat_service.handle(payload)
    except Exception as exc:
        logger.exception("Chat endpoint failed: %s", exc)
        return ChatResponse(
            answer="I'm having trouble responding right now. Please try again in a moment.",
            follow_up_question=None,
            actions=[],
            sources=[],
            safety_notes=None,
        )
