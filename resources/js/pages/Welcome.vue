<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

type Faq = { slug: string; question: string };
type Turn = {
    role: 'assistant' | 'visitor';
    text: string;
    locale?: string;
    slug?: string | null;
};

const props = defineProps<{
    faqs: Faq[];
    llmEnabled: boolean;
    csrfToken: string;
}>();

const prompts = [
    {
        label: 'Track a package',
        question: '¿Dónde está mi pedido y cuánto tarda el envío?',
    },
    { label: 'Returns', question: 'What is your return policy?' },
    { label: 'Support hours', question: 'What are your support hours?' },
    {
        label: 'Contact support',
        question: '¿Cómo puedo contactar con atención al cliente?',
    },
];

const turns = ref<Turn[]>([
    {
        role: 'assistant',
        text: 'Welcome to Harbor Desk. Ask me about shipping, returns, support hours, or getting in touch. I’ll reply in English or Spanish.',
    },
]);
const question = ref('');
const busy = ref(false);
const error = ref('');
const conversation = ref<HTMLElement | null>(null);

function isSpanish(text: string): boolean {
    return /[¿¡ñáéíóú]|(el|la|los|las|que|como|donde|cuando|cuanto|mi|envio|pedido|devolucion|horario|atencion|ayuda|puedo)/i.test(
        text,
    );
}

async function ask(value = question.value): Promise<void> {
    const submitted = value.trim();
    if (!submitted || busy.value) return;

    const locale = isSpanish(submitted) ? 'es' : 'en';
    turns.value.push({ role: 'visitor', text: submitted });
    question.value = '';
    error.value = '';
    busy.value = true;
    await nextTick();
    conversation.value?.scrollTo({
        top: conversation.value.scrollHeight,
        behavior: 'smooth',
    });

    try {
        const response = await fetch('/ask', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: JSON.stringify({ question: submitted }),
        });

        if (!response.ok) throw new Error('Request failed');

        const result = (await response.json()) as {
            locale: string;
            answer: string;
            matched_slug: string | null;
        };
        turns.value.push({
            role: 'assistant',
            text: result.answer,
            locale: result.locale,
            slug: result.matched_slug,
        });
    } catch {
        turns.value.push({
            role: 'assistant',
            text:
                locale === 'es'
                    ? 'No he podido conectar ahora. Vuelve a intentarlo en un momento o escribe a support@harbordesk.example.'
                    : 'I couldn’t connect just now. Please try again in a moment or email support@harbordesk.example.',
            locale,
        });
    } finally {
        busy.value = false;
        await nextTick();
        conversation.value?.scrollTo({
            top: conversation.value.scrollHeight,
            behavior: 'smooth',
        });
    }
}
</script>

<template>
    <Head title="Harbor Desk FAQ" />

    <div class="min-h-screen bg-[#f5f7f9] text-[#182c3b]">
        <header
            class="flex h-[72px] items-center justify-between border-b border-[#e7ecef] bg-white px-5 sm:px-8"
        >
            <a
                href="/"
                class="flex items-center gap-3"
                aria-label="Harbor Desk home"
            >
                <span
                    class="grid size-10 place-items-center rounded-xl bg-[#153f46] text-white shadow-sm"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        class="size-6"
                        aria-hidden="true"
                    >
                        <path
                            d="M3.5 18.5 12 4l8.5 14.5h-5.2L12 12.7l-3.3 5.8H3.5Z"
                            fill="currentColor"
                        />
                        <path
                            d="M7 20h10"
                            stroke="#9ed5c7"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                <span>
                    <span
                        class="block text-[15px] font-semibold tracking-[-0.02em]"
                        >Harbor Desk</span
                    >
                    <span
                        class="block text-[11px] font-medium tracking-[0.08em] text-[#788993] uppercase"
                        >Help center</span
                    >
                </span>
            </a>
            <div class="flex items-center gap-3">
                <span
                    class="hidden items-center gap-2 rounded-full border border-[#e5ebed] bg-[#fbfcfc] px-3 py-1.5 text-xs font-medium text-[#667984] sm:flex"
                >
                    <span class="size-1.5 rounded-full bg-[#56a88e]"></span>
                    Harbor Desk assistant
                </span>
                <span
                    class="grid size-9 place-items-center rounded-full bg-[#eaf1ef] text-xs font-semibold text-[#315e59]"
                    >HD</span
                >
            </div>
        </header>

        <main class="mx-auto max-w-[1440px] px-4 py-7 sm:px-8 sm:py-10">
            <section
                class="mb-7 flex flex-col justify-between gap-5 sm:flex-row sm:items-end"
            >
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.16em] text-[#58877b] uppercase"
                    >
                        Customer support · Demo workspace
                    </p>
                    <h1
                        class="text-[28px] leading-tight font-semibold tracking-[-0.04em] text-[#1c3443] sm:text-[34px]"
                    >
                        A little help goes a long way.
                    </h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-[#72818a]">
                        Ask a question in English or Spanish. Harbor Desk finds
                        the right answer and keeps the conversation in your
                        language.
                    </p>
                </div>
                <div
                    class="flex w-fit items-center gap-2 rounded-lg border border-[#e3e9eb] bg-white px-3.5 py-2.5 text-xs text-[#657984] shadow-[0_2px_8px_rgba(20,50,60,0.03)]"
                >
                    <svg
                        viewBox="0 0 20 20"
                        fill="none"
                        class="size-4 text-[#488876]"
                        aria-hidden="true"
                    >
                        <path
                            d="M10 2.5a7.5 7.5 0 1 0 7.5 7.5M10 2.5v7.25h7.25M6 10h3m-3 3h6"
                            stroke="currentColor"
                            stroke-width="1.4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span>{{
                        llmEnabled
                            ? 'AI-assisted answers'
                            : 'Instant FAQ answers'
                    }}</span>
                    <span class="mx-0.5 h-4 w-px bg-[#e6ebed]"></span>
                    <span>EN · ES</span>
                </div>
            </section>

            <section
                class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <div
                    class="overflow-hidden rounded-2xl border border-[#e5eaec] bg-white shadow-[0_10px_35px_rgba(29,57,68,0.045)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#edf0f1] px-5 py-4 sm:px-6"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="grid size-9 place-items-center rounded-full bg-[#e9f2ef] text-[#397767]"
                            >
                                <svg
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    class="size-[18px]"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M4 5.5h12v8H9l-3.5 2v-2H4v-8Z"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linejoin="round"
                                    />
                                    <circle
                                        cx="7.5"
                                        cy="9.5"
                                        r=".7"
                                        fill="currentColor"
                                    />
                                    <circle
                                        cx="10"
                                        cy="9.5"
                                        r=".7"
                                        fill="currentColor"
                                    />
                                    <circle
                                        cx="12.5"
                                        cy="9.5"
                                        r=".7"
                                        fill="currentColor"
                                    />
                                </svg>
                            </span>
                            <div>
                                <h2
                                    class="text-sm font-semibold text-[#263d4a]"
                                >
                                    Chat with Harbor Desk
                                </h2>
                                <p class="mt-0.5 text-xs text-[#87959c]">
                                    Usually replies instantly
                                </p>
                            </div>
                        </div>
                        <span
                            class="flex items-center gap-1.5 rounded-full bg-[#eef7f2] px-2.5 py-1 text-[11px] font-semibold text-[#42816b]"
                        >
                            <span
                                class="size-1.5 rounded-full bg-[#5bb48e]"
                            ></span>
                            Online
                        </span>
                    </div>

                    <div
                        ref="conversation"
                        class="flex h-[min(52vh,460px)] min-h-[320px] flex-col gap-5 overflow-y-auto bg-[#fbfcfc] px-5 py-6 sm:px-7"
                    >
                        <div
                            v-for="(turn, index) in turns"
                            :key="index"
                            :class="
                                turn.role === 'visitor'
                                    ? 'ml-auto max-w-[88%] items-end'
                                    : 'mr-auto max-w-[90%] items-start'
                            "
                            class="flex flex-col gap-1.5"
                        >
                            <span
                                class="px-1 text-[10px] font-semibold tracking-[0.12em] text-[#92a0a6] uppercase"
                                >{{
                                    turn.role === 'visitor'
                                        ? 'You'
                                        : 'Harbor Desk'
                                }}</span
                            >
                            <div
                                :class="
                                    turn.role === 'visitor'
                                        ? 'rounded-2xl rounded-br-md bg-[#194d50] text-white'
                                        : 'rounded-2xl rounded-bl-md border border-[#e8edef] bg-white text-[#344b57]'
                                "
                                class="px-4 py-3 text-[13px] leading-[1.75] shadow-[0_2px_5px_rgba(20,50,60,0.025)] sm:px-5"
                            >
                                {{ turn.text }}
                                <div
                                    v-if="
                                        turn.role === 'assistant' && turn.locale
                                    "
                                    class="mt-2.5 flex items-center gap-2 border-t border-[#edf0f1] pt-2"
                                >
                                    <span
                                        class="rounded bg-[#f0f5f3] px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-[#497b6e] uppercase"
                                        >{{ turn.locale }}</span
                                    >
                                    <span class="text-[10px] text-[#8a989d]">{{
                                        turn.slug
                                            ? 'Matched FAQ · ' + turn.slug
                                            : 'Harbor Desk support'
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="busy"
                            class="mr-auto flex items-center gap-2 rounded-2xl border border-[#e8edef] bg-white px-4 py-3 text-xs text-[#829197]"
                        >
                            <span class="flex gap-1"
                                ><i
                                    class="size-1.5 animate-bounce rounded-full bg-[#6b9c8c]"
                                ></i
                                ><i
                                    class="size-1.5 animate-bounce rounded-full bg-[#6b9c8c] [animation-delay:120ms]"
                                ></i
                                ><i
                                    class="size-1.5 animate-bounce rounded-full bg-[#6b9c8c] [animation-delay:240ms]"
                                ></i
                            ></span>
                            Finding the right answer…
                        </div>
                    </div>

                    <div class="border-t border-[#edf0f1] bg-white p-4 sm:p-5">
                        <div class="mb-3 flex flex-wrap gap-2">
                            <button
                                v-for="prompt in prompts"
                                :key="prompt.label"
                                type="button"
                                class="rounded-full border border-[#e4eaeb] bg-white px-3 py-1.5 text-[11px] font-medium text-[#637780] transition hover:border-[#99b9ad] hover:bg-[#f5faf7] hover:text-[#356b5d] disabled:opacity-50"
                                :disabled="busy"
                                @click="ask(prompt.question)"
                            >
                                {{ prompt.label }}
                            </button>
                        </div>
                        <form
                            class="flex items-end gap-2 rounded-xl border border-[#dfe7e8] bg-[#fcfdfd] p-2 transition focus-within:border-[#80a99d] focus-within:ring-3 focus-within:ring-[#e8f2ee]"
                            @submit.prevent="ask()"
                        >
                            <label class="sr-only" for="question"
                                >Ask Harbor Desk</label
                            >
                            <textarea
                                id="question"
                                v-model="question"
                                rows="1"
                                maxlength="1000"
                                placeholder="Ask a question in English or Spanish…"
                                class="max-h-32 min-h-10 flex-1 resize-y bg-transparent px-2 py-2 text-[13px] leading-5 text-[#263d4a] outline-none placeholder:text-[#a3afb4]"
                                @keydown.enter.exact.prevent="ask()"
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="busy || !question.trim()"
                                class="grid size-10 shrink-0 place-items-center rounded-lg bg-[#194d50] text-white transition hover:bg-[#123e41] disabled:cursor-not-allowed disabled:opacity-40"
                                aria-label="Send question"
                            >
                                <svg
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    class="size-[18px]"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="m3.5 10 12-6-3.8 12-2.3-4.2L3.5 10Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="m9.4 11.8 4.2-4.3"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </button>
                        </form>
                        <p class="mt-2 px-1 text-[10px] text-[#a0aaae]">
                            Harbor Desk answers from its verified FAQ. No
                            personal details needed.
                        </p>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div
                        class="rounded-2xl border border-[#e5eaec] bg-white p-5 shadow-[0_10px_35px_rgba(29,57,68,0.035)]"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2
                                    class="text-sm font-semibold text-[#263d4a]"
                                >
                                    Popular questions
                                </h2>
                                <p class="mt-1 text-xs text-[#87959c]">
                                    Tap one to try it out
                                </p>
                            </div>
                            <span
                                class="rounded-md bg-[#f1f5f4] px-2 py-1 text-[10px] font-semibold text-[#64877d]"
                                >{{ faqs.length }} FAQs</span
                            >
                        </div>
                        <div class="divide-y divide-[#eef1f2]">
                            <button
                                v-for="faq in faqs"
                                :key="faq.slug"
                                type="button"
                                :disabled="busy"
                                class="group flex w-full items-center justify-between gap-3 py-3 text-left first:pt-1 last:pb-1 disabled:opacity-50"
                                @click="ask(faq.question)"
                            >
                                <span
                                    class="text-[12px] leading-5 text-[#556b75] transition group-hover:text-[#367264]"
                                    >{{ faq.question }}</span
                                >
                                <svg
                                    viewBox="0 0 16 16"
                                    fill="none"
                                    class="size-4 shrink-0 text-[#a5b0b4] transition group-hover:translate-x-0.5 group-hover:text-[#548a7a]"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M3 8h10m-4-4 4 4-4 4"
                                        stroke="currentColor"
                                        stroke-width="1.3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl bg-[#173f44] p-5 text-white shadow-[0_10px_35px_rgba(29,57,68,0.08)]"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="rounded-md border border-white/15 bg-white/10 px-2 py-1 text-[10px] font-semibold tracking-wide text-[#b8d8ce]"
                                >HOW IT WORKS</span
                            >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                class="size-5 text-[#9bcabd]"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 3.5 14.1 9l5.4 2-5.4 2.1-2.1 5.4L9.9 13l-5.4-2 5.4-2L12 3.5Z"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <ol class="space-y-3 text-[11px] text-[#d1e1dd]">
                            <li class="flex items-center gap-2.5">
                                <span
                                    class="grid size-5 place-items-center rounded-full bg-white/10 text-[10px] font-semibold text-[#b8d8ce]"
                                    >1</span
                                >Language detected automatically
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span
                                    class="grid size-5 place-items-center rounded-full bg-white/10 text-[10px] font-semibold text-[#b8d8ce]"
                                    >2</span
                                >Answer retrieved from the FAQ
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span
                                    class="grid size-5 place-items-center rounded-full bg-white/10 text-[10px] font-semibold text-[#b8d8ce]"
                                    >3</span
                                >Optional same-language AI rewrite
                            </li>
                        </ol>
                        <div
                            class="mt-4 border-t border-white/10 pt-3 text-[10px] text-[#a8c6bf]"
                        >
                            {{
                                llmEnabled
                                    ? 'Gemini rewrite enabled'
                                    : 'Retrieval-only mode · ready without an API key'
                            }}
                        </div>
                    </div>
                </aside>
            </section>

            <footer
                class="mt-7 flex flex-col items-center justify-between gap-2 px-1 text-[10px] text-[#99a5aa] sm:flex-row"
            >
                <span>Harbor Desk · Helpful answers, in your language.</span>
                <span>Demo experience · No real customer data</span>
            </footer>
        </main>
    </div>
</template>
