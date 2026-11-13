import { fillMarkdown } from './markdown';

function formatTime(seconds) {
    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
    const rest = String(seconds % 60).padStart(2, '0');

    return `${minutes}:${rest}`;
}

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const rest = seconds % 60;

    if (hours > 0) {
        return `${hours}h ${String(minutes).padStart(2, '0')}m ${String(rest).padStart(2, '0')}s`;
    }

    return `${minutes}m ${String(rest).padStart(2, '0')}s`;
}

function watchReopen(opensAtEl, countdown) {
    const opens = Date.parse(opensAtEl.getAttribute('data-opens-at') ?? '');

    if (Number.isNaN(opens)) {
        return;
    }

    const tick = () => {
        const remaining = Math.max(0, Math.floor((opens - Date.now()) / 1000));

        countdown.textContent = remaining === 0
            ? 'You may ask now. Refresh. Or don\'t.'
            : `Come back in ${formatDuration(remaining)}. Not sooner.`;
    };

    tick();
    window.setInterval(tick, 1000);
}


function tickMesaClocks() {
    const now = new Date();
    const mesa = new Intl.DateTimeFormat('en-US', {
        timeZone: 'America/Phoenix',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(now);
    const mesa24 = new Intl.DateTimeFormat('en-US', {
        timeZone: 'America/Phoenix',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(now);

    const signal = document.querySelector('#mesa-clock');
    if (signal) {
        signal.textContent = `${mesa.toUpperCase()} MST`;
    }

    const phone = document.querySelector('#phone-clock');
    if (phone) {
        phone.textContent = mesa24;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    tickMesaClocks();
    window.setInterval(tickMesaClocks, 1000);

    const opensAtEl = document.querySelector('[data-opens-at]');
    const reopenCountdown = document.querySelector('#reopen-countdown');

    if (opensAtEl && reopenCountdown) {
        watchReopen(opensAtEl, reopenCountdown);
    }

    const countdown = document.querySelector('#countdown');
    const log = document.querySelector('#log');
    const form = document.querySelector('#ask');
    const question = document.querySelector('#question');
    const error = document.querySelector('#ask-error');
    const onAir = document.querySelector('#on-air');
    const quota = document.querySelector('#quota');
    const empty = document.querySelector('#imessage-empty');
    const dock = document.querySelector('#ask-dock');
    const studio = document.querySelector('#studio');
    const plus = document.querySelector('#ask-plus');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (countdown) {
        let remaining = 14 * 60 + 59;

        window.setInterval(() => {
            remaining = remaining <= 1 ? 14 * 60 + 59 : remaining - 1;
            countdown.textContent = formatTime(remaining);
        }, 1000);
    }

    if (dock && studio && 'IntersectionObserver' in window) {
        const hideDock = (hidden) => {
            dock.classList.toggle('translate-y-full', hidden);
            dock.classList.toggle('opacity-0', hidden);
            dock.classList.toggle('pointer-events-none', hidden);
            dock.setAttribute('aria-hidden', hidden ? 'true' : 'false');
        };

        new IntersectionObserver(([entry]) => {
            hideDock(entry.isIntersecting);
        }, { threshold: 0.2 }).observe(studio);
    }

    if (! log || ! form || ! question || ! error || ! token) {
        return;
    }

    const endpoint = form.dataset.endpoint;

    let currentAskId = null;
    let activeAudio = null;

    function addPlay(bubble, id) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'play-reply';
        btn.setAttribute('aria-label', 'Play this reply');
        btn.textContent = 'Play';

        btn.addEventListener('click', async () => {
            if (activeAudio && ! activeAudio.paused && btn.dataset.playing === '1') {
                activeAudio.pause();
                activeAudio.currentTime = 0;
                btn.dataset.playing = '0';
                btn.textContent = 'Play';

                return;
            }

            error.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = '…';

            try {
                const response = await fetch(`/asks/${id}/speak`, {
                    method: 'POST',
                    headers: {
                        Accept: 'audio/mpeg, application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });

                if (! response.ok) {
                    const data = await response.json().catch(() => ({}));
                    error.textContent = data.message ?? 'Voice is not available.';
                    error.classList.remove('hidden');
                    btn.textContent = 'Play';
                    btn.disabled = false;

                    return;
                }

                if (activeAudio) {
                    activeAudio.pause();
                }

                const url = URL.createObjectURL(await response.blob());
                const audio = new Audio(url);
                activeAudio = audio;
                btn.dataset.playing = '1';
                btn.textContent = 'Stop';
                btn.disabled = false;

                audio.addEventListener('ended', () => {
                    URL.revokeObjectURL(url);
                    btn.dataset.playing = '0';
                    btn.textContent = 'Play';
                });

                await audio.play();
            } catch {
                error.textContent = 'Voice is not available.';
                error.classList.remove('hidden');
                btn.textContent = 'Play';
                btn.disabled = false;
            }
        });

        bubble.appendChild(btn);
    }

    const button = form.querySelector('button[type="submit"]');

    const resizeQuestion = () => {
        question.style.height = 'auto';
        question.style.height = `${Math.min(question.scrollHeight, 128)}px`;

        if (button) {
            button.classList.toggle('invisible', question.value.trim() === '');
            button.disabled = question.value.trim() === '';
        }
    };

    question.addEventListener('input', resizeQuestion);
    resizeQuestion();

    question.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && ! event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    plus?.addEventListener('click', () => {
        error.textContent = 'Photos are extra. Dignity is extra. Type the question.';
        error.classList.remove('hidden');
        question.focus();
    });

    dock?.addEventListener('click', () => {
        window.setTimeout(() => question.focus(), 0);
    });

    async function readStream(response, onDelta) {
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let reply = '';

        while (true) {
            const { done, value } = await reader.read();

            if (done) {
                break;
            }

            buffer += decoder.decode(value, { stream: true });
            const parts = buffer.split('\n\n');
            buffer = parts.pop() ?? '';

            for (const part of parts) {
                const line = part.split('\n').find((row) => row.startsWith('data: '));

                if (! line) {
                    continue;
                }

                const payload = line.slice(6).trim();

                if (payload === '[DONE]') {
                    continue;
                }

                try {
                    const event = JSON.parse(payload);

                    if (event.type === 'ask' && event.id) {
                        currentAskId = event.id;
                        continue;
                    }

                    if (event.type === 'text_delta' && event.delta) {
                        reply += event.delta;
                        onDelta(reply);
                    }
                } catch {
                    // skip malformed chunks
                }
            }
        }

        return reply;
    }

    function hideEmpty() {
        empty?.remove();
    }

    function addYou(text) {
        hideEmpty();

        const el = document.createElement('div');
        el.className = 'bubble-you bubble-md';
        fillMarkdown(el, text);
        log.appendChild(el);
        log.scrollTop = log.scrollHeight;

        return el;
    }

    function addJordan() {
        hideEmpty();

        const el = document.createElement('div');
        el.className = 'bubble-jordan bubble-md';

        const words = document.createElement('div');
        words.dataset.role = 'words';
        el.appendChild(words);

        const caret = document.createElement('span');
        caret.className = 'caret motion-reduce:hidden';
        caret.setAttribute('aria-hidden', 'true');
        el.appendChild(caret);

        log.appendChild(el);
        log.scrollTop = log.scrollHeight;

        return el;
    }

    function addTyping() {
        hideEmpty();

        const el = document.createElement('div');
        el.className = 'bubble-jordan';
        el.dataset.role = 'typing';
        el.setAttribute('aria-label', 'Jordan is typing');
        el.innerHTML = '<span class="typing-dots" aria-hidden="true"><span class="typing-dots-dot"></span><span class="typing-dots-dot [animation-delay:150ms]"></span><span class="typing-dots-dot [animation-delay:300ms]"></span></span>';
        log.appendChild(el);
        log.scrollTop = log.scrollHeight;

        return el;
    }

    function addReceipt(text) {
        const el = document.createElement('p');
        el.className = 'self-end px-1 text-[11px] text-black/40';
        el.textContent = text;
        log.appendChild(el);
        log.scrollTop = log.scrollHeight;
    }

    function addStamp(text) {
        const el = document.createElement('p');
        el.className = 'mx-auto py-1 text-center text-[11px] font-medium text-black/35';
        el.textContent = text;
        log.appendChild(el);
    }

    function setLive(live) {
        if (! onAir) {
            return;
        }

        onAir.classList.toggle('hidden', ! live);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        currentAskId = null;
        const text = question.value.trim();

        if (text === '') {
            error.textContent = 'THAT IS NOT A QUESTION. THAT IS A BLANK BOX. THE SMART PERSON IS DISAPPOINTED IN YOU AND THEY HAVE NOT EVEN MET YOU.';
            error.classList.remove('hidden');
            question.focus();

            return;
        }

        error.classList.add('hidden');
        addYou(text);
        addReceipt('Delivered. We own this now.');
        question.value = '';
        resizeQuestion();
        question.disabled = true;

        if (button) {
            button.disabled = true;
        }

        const typing = addTyping();
        setLive(true);

        let reply = '';
        let soldOut = false;
        let bubble = null;
        let words = null;
        let caret = null;

        const openBubble = () => {
            if (bubble) {
                return;
            }

            typing.remove();
            bubble = addJordan();
            words = bubble.querySelector('[data-role="words"]');
            caret = bubble.querySelector('.caret');
        };

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ question: text }),
            });

            const remaining = response.headers.get('X-RateLimit-Remaining');

            if (remaining !== null && quota && remaining !== '0') {
                quota.textContent = `${remaining} questions left today. That's the allowance. Not a suggestion.`;
            }

            if (! response.ok) {
                const data = await response.json().catch(() => ({}));
                reply = data.message ?? 'Ask failed.';
                soldOut = response.status === 429;
                openBubble();
                fillMarkdown(words, reply);
            } else if (response.headers.get('content-type')?.includes('text/event-stream')) {
                reply = await readStream(response, (full) => {
                    if (! bubble) {
                        openBubble();
                    }

                    fillMarkdown(words, full);
                    log.scrollTop = log.scrollHeight;
                });

                if (! bubble) {
                    openBubble();
                }

                if (reply === '') {
                    reply = 'No words came out. The pipes were empty.';
                }

                fillMarkdown(words, reply);
            } else {
                const data = await response.json();
                reply = data.reply ?? 'Ask failed.';
                if (data.id) {
                    currentAskId = data.id;
                }
                openBubble();
                fillMarkdown(words, reply);
            }

            if (remaining === '0') {
                soldOut = true;
            }
        } catch {
            reply = 'THE SMART PERSON IS IN THE BATHROOM. TRY AGAIN.';
            openBubble();
            fillMarkdown(words, reply);
        }

        caret?.remove();

        if (bubble && currentAskId) {
            addPlay(bubble, currentAskId);
        }

        setLive(false);

        if (soldOut) {
            addStamp('Read. Shop closed.');
            question.disabled = true;
            window.setTimeout(() => window.location.reload(), 2200);
        } else {
            addStamp('Read. Pipelines did this.');
            question.disabled = false;
            resizeQuestion();
            question.focus();
        }
    });
});
