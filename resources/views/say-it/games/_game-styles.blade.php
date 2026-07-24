{{-- Shared game polish --}}
@push('styles')
<style>
@keyframes sayit-dice-spin {
    0% { transform: rotateX(0) rotateY(0) scale(1); }
    40% { transform: rotateX(420deg) rotateY(280deg) scale(1.15); }
    100% { transform: rotateX(720deg) rotateY(360deg) scale(1); }
}
@keyframes sayit-bob {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
@keyframes sayit-wave {
    0% { background-position: 0 0; }
    100% { background-position: 40px 0; }
}
@keyframes sayit-pulse-soft {
    0%, 100% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.35); }
    50% { box-shadow: 0 0 0 8px rgba(124, 58, 237, 0); }
}
@keyframes sayit-tile-pop {
    0% { transform: scale(0.85); opacity: 0.6; }
    100% { transform: scale(1); opacity: 1; }
}
@keyframes sayit-confetti {
    0% { transform: translateY(0) rotate(0); opacity: 1; }
    100% { transform: translateY(80px) rotate(240deg); opacity: 0; }
}
@keyframes sayit-token-hop {
    0% { transform: translateY(0) scale(1); }
    40% { transform: translateY(-10px) scale(1.15); }
    100% { transform: translateY(0) scale(1); }
}
.sayit-dice-rolling { animation: sayit-dice-spin 0.7s ease-out; }
.sayit-bob { animation: sayit-bob 1.4s ease-in-out infinite; }
.sayit-tile-pop { animation: sayit-tile-pop 0.18s ease-out; }
.sayit-token-hop { animation: sayit-token-hop 0.35s ease-out; }
.sayit-turn-pulse { animation: sayit-pulse-soft 1.6s ease-in-out infinite; }
.sayit-scrabble-wood {
    background:
        radial-gradient(ellipse at 20% 20%, rgba(255,255,255,0.12), transparent 50%),
        linear-gradient(145deg, #8b5a2b 0%, #6b4226 40%, #5a361f 100%);
}
.sayit-tile {
    background: linear-gradient(160deg, #f7e7c3 0%, #e8d4a8 55%, #d9c28f 100%);
    border: 1px solid #c4a574;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.55), 0 2px 4px rgba(0,0,0,0.18);
    color: #2c1810;
    position: relative;
    font-weight: 800;
    border-radius: 0.35rem;
    user-select: none;
}
.sayit-tile .pts {
    position: absolute;
    right: 2px;
    bottom: 1px;
    font-size: 0.55rem;
    font-weight: 700;
    opacity: 0.7;
    line-height: 1;
}
.sayit-tile-selected {
    outline: 2px solid #d97706;
    outline-offset: 2px;
    transform: translateY(-3px);
}
.sayit-snakes-board {
    background: linear-gradient(180deg, #ecfdf5 0%, #d1fae5 100%);
    border: 3px solid #065f46;
}
.sayit-duck-water {
    background:
        repeating-linear-gradient(90deg, rgba(255,255,255,0.15) 0 8px, transparent 8px 16px),
        linear-gradient(180deg, #7dd3fc 0%, #38bdf8 35%, #0ea5e9 70%, #0284c7 100%);
    background-size: 40px 100%, 100% 100%;
    animation: sayit-wave 2.5s linear infinite;
}
.sayit-pic-frame {
    background: linear-gradient(145deg, #1e1b4b, #312e81);
    box-shadow: inset 0 0 0 3px rgba(255,255,255,0.12), 0 8px 20px rgba(76, 29, 149, 0.25);
}
.sayit-letter-bank {
    background: linear-gradient(180deg, #f5f3ff, #ede9fe);
}
</style>
@endpush
