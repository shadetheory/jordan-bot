<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>THE SMART PERSON™ — Talk to Jordan</title>
        <meta name="description" content="Not just an AI. We run your question through data pipelines and produce a great AI slop answer. Operators are sitting by.">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen overflow-x-hidden bg-black font-sans text-[#f4f4f4]">
        @if ($closed)
            <main class="scanlines flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-12 text-center">
                <p class="bg-gold px-3 py-1 font-sans text-xs font-semibold tracking-widest text-black uppercase">Shop is down</p>
                <h1 class="font-yell text-6xl text-hot uppercase sm:text-8xl">CLOSED</h1>
                <p class="max-w-xl font-bang text-3xl text-[#f4f4f4]">GO AWAY</p>
                <p class="max-w-xl text-xl text-[#f4f4f4]">
                    We answered all the questions we want to answer. Do not pester us.
                </p>
                <p class="max-w-xl text-lg text-[#f4f4f4]">
                    You may ask again at
                    <time data-opens-at="{{ $opensAt->toIso8601String() }}" datetime="{{ $opensAt->toIso8601String() }}">
                        {{ $opensAt->timezone(config('app.timezone'))->format('D, M j, Y g:i A T') }}
                    </time>.
                </p>
                <p id="reopen-countdown" class="font-sans font-semibold uppercase tracking-wide text-2xl text-hot uppercase"></p>
                <div class="mx-auto grid w-full max-w-3xl gap-4 sm:grid-cols-2">
                    <figure class="relative">
                        <img
                            src="{{ asset('images/infomercial/host.jpg') }}"
                            alt="An infomercial host pointing at you, which is not a yes."
                            class="w-full border-8 border-hot grayscale"
                        >
                        <p class="absolute inset-x-4 bottom-4 bg-hot px-2 py-1 font-sans font-semibold uppercase tracking-wide text-xl uppercase">NO</p>
                    </figure>
                    <figure class="relative">
                        <img
                            src="{{ asset('images/infomercial/jordan.jpg') }}"
                            alt="Jordan Partridge, a real person, from his GitHub profile."
                            class="w-full border-8 border-hot grayscale"
                        >
                        <p class="absolute inset-x-4 bottom-4 bg-hot px-2 py-1 font-sans text-sm font-semibold tracking-wide text-white uppercase">CLOSED</p>
                    </figure>
                </div>
                <p class="max-w-md text-sm text-[#f4f4f4]/75">Until then the pipelines are off. Operators hung up. Preferably don't come back.</p>
            </main>
        @else
        <a href="#ask" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-gold focus:px-4 focus:py-2 focus:text-studio">
            Skip the screaming and ask already
        </a>

        <div class="sticky top-0 z-40 overflow-hidden border-b-4 border-gold bg-neon text-hot" aria-hidden="true">
            <div class="flex w-max motion-safe:animate-marquee motion-reduce:animate-none">
                @foreach ([1, 2] as $copy)
                    <p class="flex shrink-0 items-center gap-8 px-6 py-2 font-yell text-lg tracking-wide uppercase sm:text-2xl">
                        <span>As seen on NOTHING</span>
                        <span>★</span>
                        <span>THE PART THAT DECIDES</span>
                        <span>★</span>
                        <span>Operators are sitting by</span>
                        <span>★</span>
                        <span>Ask a Really Smart Person</span>
                        <span>★</span>
                        <span>Talk to Jordan</span>
                        <span>★</span>
                        <span>A real person. Not an AI.</span>
                        <span>★</span>
                        <span>SMALL CALLS. NAH. LATER.</span>
                        <span>★</span>
                        <span>Not just an AI</span>
                        <span>★</span>
                        <span>Data pipelines</span>
                        <span>★</span>
                        <span>Great AI slop</span>
                        <span>★</span>
                        <span>Dignity not included.</span>
                        <span>★</span>
                    </p>
                @endforeach
            </div>
        </div>

        <header class="scanlines relative overflow-hidden border-b-8 border-gold bg-black">
            <div class="relative mx-auto grid max-w-6xl items-center gap-8 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:py-16">
                <div class="flex flex-col gap-5">
                    <p class="w-fit bg-gold px-3 py-1 font-sans text-xs font-semibold tracking-[0.25em] text-black uppercase motion-safe:animate-flash motion-reduce:animate-none">
                        SIGNAL LIVE · <span id="mesa-clock">--:-- MST</span> · the part that decides
                    </p>
                    <p class="font-sans text-2xl text-[#f4f4f4] sm:text-3xl">Tired of asking an AI and getting AI slop???</p>
                    <h1 class="font-yell text-5xl leading-[0.9] text-[#f4f4f4] uppercase drop-shadow-[4px_4px_0_#8b0000] sm:text-7xl">
                        Talk to Jordan
                    </h1>
                    <p class="font-sans text-xl text-[#f4f4f4] sm:text-2xl">
                        What if we ran your question through data pipelines first
                    </p>
                    <p class="max-w-xl text-lg text-[#f4f4f4] sm:text-xl">
                        Introducing <span class="bg-gold px-1 text-studio">THE SMART PERSON™</span> —
                        it is not just an AI slop answer. We ingest. We enrich. We index.
                        Then a computer. Then slop polish. You receive a
                        <span class="bg-hot px-1 text-white">great AI slop</span> answer.
                        Ask a really smart person. The pipelines are why it costs dignity.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a
                            href="#ask"
                            class="inline-flex items-center justify-center border-4 border-white bg-hot px-8 py-4 font-sans font-semibold uppercase tracking-wide text-2xl uppercase shadow-[6px_6px_0_#ffd400] hover:bg-velvet"
                        >
                            ASK NOW
                        </a>
                        <span class="font-bang text-3xl text-hot motion-safe:animate-wobble motion-reduce:animate-none" aria-hidden="true">★ NEW ★</span>
                        <p class="text-[#f4f4f4]">
                            Offer expires in
                            <span id="countdown" class="bg-black px-2 text-hot">14:59</span>
                            (it always does)
                        </p>
                    </div>
                    <p class="text-sm text-[#f4f4f4]/75">
                        *If you do not ask in the next 14 minutes, you will still have a question. We cannot help that. That is your whole personality.
                    </p>
                </div>
                <div class="relative mx-auto w-full max-w-lg">
                    <figure class="relative">
                        <div class="absolute inset-0 rotate-2 bg-neon"></div>
                        <img
                            src="{{ asset('images/infomercial/jordan.jpg') }}"
                            alt="Jordan Partridge, a real person, the Smart Person, from his GitHub profile."
                            class="relative z-10 w-full -rotate-1 border-8 border-gold shadow-[0_0_0_10px_#ff2d2d]"
                        >
                        <p class="absolute left-3 top-3 z-20 bg-hot px-2 py-1 font-sans font-semibold uppercase tracking-wide text-sm uppercase">THIS IS THE PRODUCT</p>
                        <p class="absolute right-3 top-3 z-20 bg-neon px-2 py-1 font-sans font-semibold uppercase tracking-wide text-sm text-studio uppercase">JORDAN</p>
                        <p class="absolute inset-x-3 bottom-3 z-20 bg-black/85 px-2 py-2 text-center font-sans text-sm font-semibold tracking-wide text-gold uppercase sm:text-base">
                            ASK THIS FACE
                        </p>
                    </figure>
                    <p class="relative z-10 mt-4 bg-black px-3 py-2 text-center font-sans text-sm font-semibold tracking-wide text-gold uppercase">
                        He's the offer.
                    </p>
                </div>
            </div>
        </header>

        <section class="border-b-8 border-gold bg-black px-4 py-10 sm:px-6">
            <div class="mx-auto grid max-w-5xl items-center gap-8 lg:grid-cols-2">
                <figure class="relative overflow-hidden border-8 border-gold bg-black shadow-[8px_8px_0_#ff2d2d]">
                    <div class="relative aspect-video overflow-hidden">
                        <img
                            src="{{ asset('images/infomercial/jordan.jpg') }}"
                            alt="Jordan Partridge, a real person, from his GitHub profile, as if this were a talking-head tape."
                            class="absolute inset-0 size-full object-cover object-[center_20%] motion-safe:ken-burns motion-reduce:transform-none"
                        >
                        <div class="scanlines pointer-events-none absolute inset-0"></div>
                        <p class="absolute left-3 top-3 z-10 flex items-center gap-2 bg-black/80 px-2 py-1 font-sans font-semibold uppercase tracking-wide text-sm text-hot uppercase">
                            <span class="rec-dot inline-block size-2 rounded-full bg-hot"></span>
                            REC · A WORD FROM THE HUMAN
                        </p>
                        <p class="absolute right-3 top-3 z-10 bg-black/80 px-2 py-1 font-sans font-semibold uppercase tracking-wide text-xs text-gold uppercase">MUTED ON PURPOSE</p>
                        <p class="absolute inset-x-0 bottom-0 z-10 bg-black/80 px-3 py-2 text-center text-sm text-[#f4f4f4] sm:text-base">
                            “Hi. I'm Jordan. I'm a real person. Not an AI. You can ask me a question.”
                        </p>
                    </div>
                    <figcaption class="flex items-center justify-between bg-black px-3 py-2 text-xs font-semibold tracking-wide text-gold">
                        <span>VHS · TRACK 1 · TALKING HEAD</span>
                        <span>PLAY ▶</span>
                    </figcaption>
                </figure>
                <div class="flex flex-col gap-3">
                    <h2 class="font-sans text-2xl text-[#f4f4f4]">A word from the human</h2>
                    <p class="text-lg text-[#f4f4f4]">
                        We wanted a short video of him saying that. The tape is still in the machine.
                        This is the still. The caption is the line. Same energy. Less budget.
                    </p>
                    <p class="text-sm text-[#f4f4f4]/75">
                        That is his actual face, from
                        <a href="https://github.com/jordanpartridge" class="underline">github.com/jordanpartridge</a>.
                        He is a real person. He bikes. He is not an AI.
                    </p>
                </div>
            </div>
        </section>

        <section class="border-b-8 border-gold bg-black px-4 py-12 sm:px-6">
            <div class="mx-auto flex max-w-5xl flex-col gap-6">
                <h2 class="text-center font-sans text-3xl text-[#f4f4f4] sm:text-5xl">Do you suffer from… QUESTIONS???</h2>
                <ul class="grid gap-3 text-lg font-bold sm:grid-cols-2">
                    <li class="border-4 border-gold bg-black p-4">Googling at 3am like the search bar owes you rent</li>
                    <li class="border-4 border-gold bg-black p-4">Asking a chatbot and receiving amateur slop. No hopper. No pipes. Shameful.</li>
                    <li class="border-4 border-gold bg-black p-4">Asking your group chat and getting a GIF of a raccoon</li>
                    <li class="border-4 border-gold bg-black p-4">Asking yourself, which is just thinking, which is exhausting</li>
                </ul>
                <p class="text-center font-sans text-2xl text-[#f4f4f4]">THERE HAS TO BE A BETTER WAY. THERE IS. IT IS PIPELINES. THEN SLOP.</p>
            </div>
        </section>

        <section class="border-b-8 border-hot bg-black px-4 py-12 text-[#f4f4f4] sm:px-6">
            <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-2">
                <figure class="flex flex-col gap-3">
                    <p class="w-fit bg-hot px-3 py-1 font-sans font-semibold uppercase tracking-wide text-2xl text-white">BEFORE</p>
                    <img
                        src="{{ asset('images/infomercial/before.jpg') }}"
                        alt="A person at 3am, spiritually defeated by a search box."
                        class="w-full border-8 border-studio grayscale"
                    >
                    <figcaption class="font-bold">
                        You. 3am. Arguing with a glowing rectangle. No Smart Person in sight. Tragic.
                    </figcaption>
                </figure>
                <figure class="flex flex-col gap-3">
                    <p class="w-fit bg-studio px-3 py-1 font-sans font-semibold uppercase tracking-wide text-2xl text-gold">AFTER</p>
                    <img
                        src="{{ asset('images/infomercial/after.jpg') }}"
                        alt="The same person, now glowing, because a smart person said words."
                        class="w-full border-8 border-gold"
                    >
                    <figcaption class="font-bold">
                        You, but SPARKLY, because a really smart person answered you. Side effects include telling strangers at parties that you “know a guy.”
                    </figcaption>
                </figure>
            </div>
        </section>

        <section class="border-b-8 border-gold bg-black px-4 py-12 sm:px-6">
            <div class="mx-auto grid max-w-6xl items-center gap-8 lg:grid-cols-2">
                <figure>
                    <img
                        src="{{ asset('images/infomercial/pipeline.jpg') }}"
                        alt="A fake late-night infomercial data pipeline factory with gold tubes, hoppers, and a man who believes in science."
                        class="w-full border-8 border-gold"
                    >
                </figure>
                <div class="flex flex-col gap-4">
                    <h2 class="font-sans text-3xl text-[#f4f4f4] sm:text-4xl">This is the product</h2>
                    <p class="text-xl text-[#f4f4f4]">
                        It is not just an AI. That would be lazy. We send your question through
                        <span class="bg-gold text-studio">data pipelines</span>
                        so the slop comes out artisanal.
                    </p>
                    <p class="text-lg">
                        Scientists hate this. Your uncle has a spreadsheet and thinks that counts. It does not. We have hoppers.
                    </p>
                    <ul class="flex flex-col gap-2 text-lg text-[#f4f4f4]">
                        <li>✓ Ingest. Enrich. Index. Then a computer. Then slop polish.</li>
                        <li>✓ Extra steps. Extra tubes. Extra confidence. Same slop, fancier trip.</li>
                        <li>✓ The output is still slop. It is GREAT slop. That is the upgrade.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="border-b-8 border-gold bg-black px-4 py-12 sm:px-6">
            <div class="mx-auto flex max-w-5xl flex-col gap-8">
                <h2 class="text-center font-sans text-3xl text-[#f4f4f4]">The lie, in three easy pipes</h2>
                <ol class="grid gap-4 sm:grid-cols-3">
                    <li class="flex flex-col gap-2 border-4 border-gold bg-black p-5">
                        <span class="font-sans text-4xl text-gold">1</span>
                        <p class="font-bang text-2xl">HAVE A QUESTION</p>
                        <p>Don't lie. You have at least one. It is probably about your hair, your job, or whether that leftover is still food.</p>
                    </li>
                    <li class="flex flex-col gap-2 border-4 border-gold bg-black p-5">
                        <span class="font-sans text-4xl text-gold">2</span>
                        <p class="font-bang text-2xl">WE PIPELINE IT</p>
                        <p>Ingest. Enrich. Index. A very serious computer. A guy in a jacket nods. This is not “just asking an AI.” We have steps.</p>
                    </li>
                    <li class="flex flex-col gap-2 border-4 border-gold bg-black p-5">
                        <span class="font-sans text-4xl text-gold">3</span>
                        <p class="font-bang text-2xl">GREAT AI SLOP</p>
                        <p>You receive an answer. It is slop. It is great slop. The pipelines are why you may tell people at brunch it was “processed.”</p>
                    </li>
                </ol>
                <ol class="grid gap-2 sm:grid-cols-6">
                    @foreach ([
                        'INGEST',
                        'ENRICH',
                        'INDEX',
                        'THE COMPUTER',
                        'SLOP POLISH',
                        'GREAT SLOP',
                    ] as $stage)
                        <li class="border-4 border-gold bg-black px-2 py-3 text-center font-sans text-sm font-semibold tracking-wide text-gold uppercase sm:text-base">{{ $stage }}</li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="border-b-8 border-hot bg-black px-4 py-12 text-[#f4f4f4] sm:px-6">
            <div class="mx-auto flex max-w-5xl flex-col gap-6">
                <h2 class="text-center font-sans font-semibold uppercase tracking-wide text-4xl uppercase">Real people. Real questions. Real cringe.</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <blockquote class="flex flex-col gap-3 border-4 border-gold bg-white p-4 text-black">
                        <p>“I used to ask my cat. The cat never replied. Now I ask a smart person. 10/10 would be condescended to again.”</p>
                        <footer class="font-sans font-semibold uppercase tracking-wide">— Brenda, who is real</footer>
                    </blockquote>
                    <blockquote class="flex flex-col gap-3 border-4 border-gold bg-white p-4 text-black">
                        <p>“I asked if I should get bangs. He said ‘that’s a you problem’ and I FELT SEEN.”</p>
                        <footer class="font-sans font-semibold uppercase tracking-wide">— Kyle, bangs pending</footer>
                    </blockquote>
                    <blockquote class="flex flex-col gap-3 border-4 border-gold bg-white p-4 text-black">
                        <p>“I thought it was just an AI. Then they said pipelines. I heard ingest. I heard SQL. I still got slop. But it felt expensive.”</p>
                        <footer class="font-sans font-semibold uppercase tracking-wide">— Anonymous, still in the chat</footer>
                    </blockquote>
                </div>
                <p class="text-center text-sm font-bold">Results not typical. Brenda’s cat remains unimpressed. Kyle still has the bangs question.</p>
            </div>
        </section>

        <section class="border-b-8 border-gold bg-black px-4 py-12 sm:px-6">
            <div class="mx-auto grid max-w-6xl items-center gap-8 lg:grid-cols-2">
                <figure>
                    <img
                        src="{{ asset('images/infomercial/genius.jpg') }}"
                        alt="A studio scientist holding a giant question mark balloon. Three easy payments. Call now."
                        class="w-full border-8 border-gold"
                    >
                </figure>
                <div class="flex flex-col gap-4">
                    <p class="w-fit bg-neon px-3 py-1 font-sans font-semibold uppercase tracking-wide text-xl text-studio uppercase">TODAY ONLY</p>
                    <h2 class="font-sans text-3xl text-[#f4f4f4] sm:text-4xl">3 easy payments of your question</h2>
                    <p class="text-xl text-[#f4f4f4]">
                        Why pay more?? You don't. You pay in questions. We keep them.
                        The balloon is a question. The question is the product. The product is you, briefly.
                    </p>
                    <ul class="flex flex-col gap-2 text-lg text-[#f4f4f4]">
                        <li>✓ Payment 1: you type it</li>
                        <li>✓ Payment 2: the pipelines chew it</li>
                        <li>✓ Payment 3: it is our property now</li>
                    </ul>
                    <p class="font-sans text-lg text-[#f4f4f4]">NOT AVAILABLE IN STORES. AVAILABLE IN THE BOX BELOW.</p>
                </div>
            </div>
        </section>

        <section class="border-b-8 border-gold bg-black px-4 py-10 text-center sm:px-6">
            <h2 class="font-sans font-semibold uppercase tracking-wide text-5xl uppercase motion-safe:animate-flash motion-reduce:animate-none sm:text-7xl">BUT WAIT. THERE’S MORE.</h2>
            <p class="mx-auto mt-4 max-w-3xl text-xl text-[#f4f4f4]">
                Ask in the next 14 minutes and we will throw in a SECOND question absolutely free.
                Plus a complimentary sigh. The sigh is the good part.
            </p>
            <p class="mt-2 text-sm">*The second question may also be ignored. The sigh is guaranteed.</p>
        </section>

        <section id="studio" class="border-b-8 border-gold bg-black px-4 py-12 sm:px-6">
            <div class="mx-auto grid max-w-6xl items-start gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="flex flex-col gap-4 border-8 border-gold bg-black p-6">
                    <h2 class="font-sans text-3xl text-[#f4f4f4]">Operators are sitting by</h2>
                    <p class="text-lg text-[#f4f4f4]">
                        They refuse to stand. Standing is for people who do not have a really smart person on retainer.
                    </p>
                    <p id="quota" class="border-4 border-gold bg-black p-3 font-sans text-sm font-semibold tracking-wide text-gold uppercase">
                        @if ($capped)
                            {{ $remaining }} left today. {{ $askPerDay }} questions per IP per day. That's the allowance. Not a suggestion.
                        @else
                            Shop is open. No cap while we get this standing. 5 questions per IP per day comes back later.
                        @endif
                    </p>

                    <div class="mx-auto w-full max-w-md rounded-[2.4rem] border-[10px] border-black bg-black p-2 shadow-[8px_8px_0_#ffd400]">
                        <div class="overflow-hidden rounded-[1.8rem] bg-white font-sans text-black">
                            <div class="flex items-center justify-between bg-imessage-bg px-5 pt-2 text-[11px] font-semibold text-black/70">
                                <span>Carrier</span>
                                <span class="h-4 w-24 rounded-full bg-black"></span>
                                <span id="phone-clock">--:--</span>
                            </div>
                            <div class="flex items-center gap-3 border-b border-black/10 bg-imessage-bg px-3 py-2">
                                <p class="text-imessage text-lg" aria-hidden="true">‹</p>
                                <img
                                    src="{{ asset('images/infomercial/jordan.jpg') }}"
                                    alt=""
                                    class="size-9 rounded-full object-cover"
                                >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">Jordan</p>
                                    <p class="text-xs text-black/50">iMessage · Live answer</p>
                                </div>
                                <p id="on-air" class="hidden text-xs text-black/50">
                                    <span class="typing-dots" aria-hidden="true">
                                        <span class="typing-dots-dot"></span>
                                        <span class="typing-dots-dot [animation-delay:150ms]"></span>
                                        <span class="typing-dots-dot [animation-delay:300ms]"></span>
                                    </span>
                                </p>
                            </div>

                            <div
                                id="log"
                                class="imessage-paper flex max-h-[28rem] min-h-80 flex-col gap-1 overflow-y-auto px-3 py-4"
                                aria-live="polite"
                            >
                                <p class="mx-auto py-1 text-center text-[11px] font-medium text-black/35">Today</p>
                                <p id="imessage-empty" class="m-auto px-6 text-center text-sm text-black/40">
                                    iMessage
                                    <span class="mt-1 block">Ask a really smart person. Blue is you. Gray is Jordan. Markdown counts in both colors. Return sends. Shift+Return is a new line, like a person with manners.</span>
                                </p>
                            </div>

                            <form id="ask" class="flex scroll-mt-28 items-end gap-2 border-t border-black/10 bg-imessage-bg px-2 py-2" data-endpoint="{{ route('ask.store') }}">
                                <button
                                    type="button"
                                    id="ask-plus"
                                    class="mb-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-black/10 text-xl font-semibold leading-none text-black/50 hover:bg-black/15"
                                    aria-label="Photos are extra. Type it."
                                >
                                    +
                                </button>
                                <label for="question" class="sr-only">Your question (required, emotionally)</label>
                                <textarea
                                    id="question"
                                    name="question"
                                    rows="1"
                                    required
                                    maxlength="4000"
                                    placeholder="iMessage"
                                    class="max-h-32 min-h-9 flex-1 resize-none rounded-[20px] border border-black/10 bg-white px-3 py-2 text-[15px] text-black placeholder:text-black/35"
                                ></textarea>
                                <button
                                    type="submit"
                                    class="mb-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-imessage text-white hover:bg-imessage/90 disabled:opacity-40"
                                    aria-label="ASK THE SMART PERSON NOW"
                                    disabled
                                >
                                    <svg viewBox="0 0 24 24" class="size-4 fill-current" aria-hidden="true">
                                        <path d="M4 12l1.41 1.41L11 7.83V20h2V7.83l5.59 5.58L20 12l-8-8-8 8z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <p id="ask-error" class="hidden text-hot" role="alert"></p>
                    <p class="text-sm text-[#f4f4f4]/75">
                        By asking, you agree that the answer may be correct, incorrect, or a vibe.
                        You also agree that <span class="font-bold">any question you ask becomes our property</span>.
                        We keep it. That is the trade. You get slop. We get the question.
                    </p>
                </div>

                <div class="flex flex-col gap-6">
                    <figure class="flex flex-col gap-3">
                        <img
                            src="{{ asset('images/infomercial/operator.jpg') }}"
                            alt="An operator who is sitting, not standing, giving two thumbs up under a neon sign."
                            class="w-full border-8 border-gold"
                        >
                        <figcaption class="text-center text-sm font-semibold tracking-wide text-gold">
                            CALL NOW. Or don't. The form is right there. We are not your dad.
                        </figcaption>
                    </figure>

                    <aside class="border-4 border-gold bg-black p-4">
                        <p class="font-sans text-sm font-semibold tracking-wide text-gold uppercase">Proof (we made this up)</p>
                        <ul class="mt-3 flex flex-col gap-3 text-sm">
                            <li class="border-2 border-gold p-3">
                                <p class="font-bold text-gold">As seen on NOTHING</p>
                                <p class="mt-1">A 3am time slot counts. We checked.</p>
                            </li>
                            <li class="border-2 border-gold p-3">
                                <p class="font-bold text-gold">Contains pipelines</p>
                                <p class="mt-1">Your question takes the scenic route. Then slop.</p>
                            </li>
                            <li class="border-2 border-gold p-3">
                                <p class="font-bold text-gold">4.8 stars (Jordan's mom)</p>
                                <p class="mt-1">She has not used it. She is still proud.</p>
                            </li>
                        </ul>
                    </aside>
                </div>
            </div>
        </section>

        <footer class="bg-black px-4 py-10 text-xs leading-relaxed text-[#f4f4f4]/75 sm:px-6">
            <div class="mx-auto flex max-w-5xl flex-col gap-3">
                <p class="font-sans text-sm font-semibold tracking-wide text-gold uppercase">Fine print, which is the funniest part</p>
                <p>
                    THE SMART PERSON™ is not FDA approved. It is not a doctor, a lawyer, a therapist, a search engine you already had, or your dad.
                    Side effects include sudden confidence, residual cringe, and telling people at parties that you “know a guy.”
                    If your question lasts more than four hours, you did not have a question. You had a TED talk.
                    Operators are sitting by. They will not stand. Standing is extra.
                    Not affiliated with any accredited university, game show, or uncle who “does his own research.”
                    Ask a Really Smart Person. Then maybe go to bed.
                    Five questions per IP per day on ask.jordanpartridge.us. After that, the Smart Person is in bed.
                    Not just an AI slop answer: we run data pipelines (ingest, enrich, index, a computer, slop polish) and produce a great AI slop answer. “Great” is a feeling. The pipelines are extra steps we are very serious about.
                    Any question you ask becomes our property. You hit the button, we own the question, the wording, the leftover, the 3am energy, all of it. We may keep it, reuse it, laugh at it, or feed it to the pipelines. You receive an answer. You do not receive the question back. If you did not want that, you should have googled.
                </p>
            </div>
        </footer>

        @endif
    </body>
</html>
