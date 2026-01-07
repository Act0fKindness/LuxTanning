from typing import List, Optional
from pydantic import BaseModel, Field


class ChatRequest(BaseModel):
    session_id: Optional[str] = Field(default=None)
    message: str
    customer_context: Optional[str] = Field(default=None)


class Action(BaseModel):
    type: str
    label: str
    url: Optional[str] = None


class Source(BaseModel):
    doc: str
    chunk_id: str


class ChatResponse(BaseModel):
    answer: str
    follow_up_question: Optional[str] = None
    actions: List[Action] = Field(default_factory=list)
    sources: List[Source] = Field(default_factory=list)
    safety_notes: Optional[str] = None
