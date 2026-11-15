"""Voices <dir>/voice/captions.json with the Kokoro neural TTS model (runs offline) into c00.wav ... and durations.json.

uv run --python 3.11 --with kokoro-onnx==0.4.9 --with soundfile python -I kokoro_voice.py <video-dir> <model-dir> [voice] [speed]
<model-dir> holds kokoro-v1.0.int8.onnx and voices-v1.0.bin (github.com/thewh1teagle/kokoro-onnx releases, model-files-v1.0).
"""
import json
import sys
import wave
from pathlib import Path

import numpy as np
from kokoro_onnx import Kokoro

video_dir, model_dir = Path(sys.argv[1]), Path(sys.argv[2])
voice = sys.argv[3] if len(sys.argv) > 3 else "af_heart"
speed = float(sys.argv[4]) if len(sys.argv) > 4 else 1.0
out = video_dir / "voice"
caps = json.loads((out / "captions.json").read_text(encoding="utf-8"))
tts = Kokoro(str(model_dir / "kokoro-v1.0.int8.onnx"), str(model_dir / "voices-v1.0.bin"))

durs = []
for i, c in enumerate(caps):
    text = c.get("s", "")
    if not text:
        durs.append(0)
        continue
    samples, rate = tts.create(text, voice=voice, speed=speed, lang="en-us")
    pcm = (np.clip(samples, -1, 1) * 32767).astype("<i2").tobytes()
    with wave.open(str(out / f"c{i:02d}.wav"), "wb") as w:  # plain 44-byte header, 16-bit mono
        w.setnchannels(1)
        w.setsampwidth(2)
        w.setframerate(rate)
        w.writeframes(pcm)
    durs.append(round(len(samples) / rate * 1000))
    print(i, durs[-1], flush=True)
(out / "durations.json").write_text(json.dumps(durs), encoding="ascii")
(out / "rate.txt").write_text(str(rate), encoding="ascii")
print(f"clips {sum(1 for d in durs if d)}, total {round(sum(durs) / 1000)} s, rate {rate}")
