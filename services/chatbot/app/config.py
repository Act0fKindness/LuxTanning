from functools import lru_cache
from pathlib import Path
from pydantic import BaseSettings, Field


class Settings(BaseSettings):
    gemini_api_key: str = Field(..., env="GEMINI_API_KEY")
    gemini_model: str = Field("models/gemini-2.5-flash", env="GEMINI_MODEL")
    bot_name: str = Field("LUMA", env="BOT_NAME")
    shop_name: str = Field("Luma Wellness", env="SHOP_NAME")
    shop_town: str = Field("London", env="SHOP_TOWN")
    knowledge_dir: Path = Field(Path(__file__).resolve().parents[1] / "knowledge_base")
    log_file: Path = Field(Path(__file__).resolve().parents[1] / "logs" / "chatbot.jsonl")

    class Config:
        env_file = Path(__file__).resolve().parents[2] / ".env"
        env_file_encoding = "utf-8"


@lru_cache()
def get_settings() -> Settings:
    settings = Settings()
    settings.log_file.parent.mkdir(parents=True, exist_ok=True)
    return settings
