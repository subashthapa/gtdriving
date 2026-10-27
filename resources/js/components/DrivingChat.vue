<script setup>
import { nextTick, ref } from 'vue'
import axios from 'axios'

const open = ref(false)
const busy = ref(false)
const conversation = ref(null)
const draft = ref('')
const error = ref('')
const log = ref(null)
const input = ref(null)
const launcher = ref(null)
const confirmReset = ref(false)

async function scrollToLatest() {
    await nextTick()
    if (log.value) log.value.scrollTop = log.value.scrollHeight
    input.value?.focus()
}
async function request(method, url, payload) {
    busy.value = true
    error.value = ''
    try {
        const { data } = await axios({ method, url, data: payload, headers: { Accept: 'application/json' } })
        conversation.value = data
        draft.value = ''
        confirmReset.value = false
    } catch (failure) {
        const status = failure.response?.status
        error.value = status === 419 ? 'Your session expired. Refresh this page to reconnect.'
            : status === 429 ? 'Please wait a minute before sending another message.'
            : Object.values(failure.response?.data?.errors || {}).flat()[0]
                || 'Unable to connect. Your message has not been confirmed. Please try again.'
    } finally {
        busy.value = false
        await scrollToLatest()
    }
}
async function toggle() {
    open.value = !open.value
    if (open.value) {
        if (!conversation.value) await request('get', '/chat')
        else await scrollToLatest()
    } else {
        await nextTick()
        launcher.value?.focus()
    }
}
function send(text = draft.value) {
    if (busy.value || !text.trim()) return
    return request('post', '/chat/messages', { message: text.trim() })
}
</script>

<template>
    <Teleport to="body">
        <section v-if="open" id="driving-chat" class="driving-chat" role="dialog" aria-labelledby="driving-chat-title" @keydown.esc.prevent="toggle">
            <header class="chat-header">
                <div class="chat-avatar" aria-hidden="true">GT</div>
                <div class="chat-heading"><h2 id="driving-chat-title">Your driving companion</h2><p>Automated booking & lesson assistant</p></div>
                <button type="button" class="chat-close" aria-label="Close chat" @click="toggle">×</button>
            </header>
            <div class="chat-intro">A little guidance. A confident next step.</div>
            <div ref="log" class="chat-log" role="log" aria-live="polite" aria-relevant="additions" :aria-busy="busy">
                <template v-for="(message, index) in conversation?.messages || []" :key="index">
                    <div class="chat-message" :class="message.role === 'user' ? 'chat-user' : 'chat-assistant'">
                        <span class="chat-speaker">{{ message.role === 'user' ? 'You' : 'GT assistant' }}</span>
                        <p>{{ message.text }}</p>
                    </div>
                </template>
                <p v-if="busy" class="chat-wait" role="status">{{ conversation ? 'Working on it…' : 'Connecting…' }}</p>
            </div>
            <div v-if="error" class="chat-error" role="alert">{{ error }} <button v-if="!conversation" type="button" @click="request('get', '/chat')">Retry</button></div>
            <div v-if="conversation?.choices?.length" class="chat-choices" aria-label="Suggested replies">
                <button v-for="choice in conversation.choices" :key="choice" type="button" :disabled="busy" @click="send(choice)">{{ choice }}</button>
            </div>
            <form v-if="conversation && !conversation.choices.length || conversation?.step === 'menu'" class="chat-compose" @submit.prevent="send()">
                <label for="chat-message" class="sr-only">Your message</label>
                <input id="chat-message" ref="input" v-model="draft" :type="conversation.inputType" :maxlength="2000" :disabled="busy" required :placeholder="conversation.inputType === 'date' ? '' : 'Type your reply…'" autocomplete="off" />
                <button type="submit" :disabled="busy || !draft.trim()" aria-label="Send message">Send <span aria-hidden="true">↗</span></button>
            </form>
            <footer class="chat-footer">
                <template v-if="confirmReset"><span>Clear chat and start again?</span><button :disabled="busy" @click="request('delete', '/chat')">Yes, clear</button><button @click="confirmReset = false">Keep chat</button></template>
                <template v-else><span>Details shared only with our team.</span><button :disabled="busy" @click="confirmReset = true">Start over</button></template>
            </footer>
        </section>
        <button ref="launcher" class="chat-launcher" :class="{ 'chat-launcher-open': open }" type="button" :aria-expanded="open" aria-controls="driving-chat" @click="toggle">
            <svg v-if="!open" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H4l-3 3V11.5A8.5 8.5 0 0 1 9.5 3h3a8.5 8.5 0 0 1 8.5 8.5Z"/><path d="M6 10h10M6 14h7"/></svg>
            {{ open ? 'Close chat' : 'Let’s talk driving' }}<span v-if="!open" class="chat-dot" aria-hidden="true"></span>
        </button>
    </Teleport>
</template>

<style scoped>
.driving-chat{position:fixed;z-index:1000;right:24px;bottom:96px;width:min(410px,calc(100vw - 32px));height:min(650px,calc(100dvh - 125px));display:flex;flex-direction:column;background:#fff;border:1px solid #dbe3ee;border-radius:20px;box-shadow:0 18px 70px #0f172a30;overflow:hidden;text-align:left;color:#17253e;font-family:inherit}
.chat-header{display:flex;align-items:center;gap:12px;padding:20px 18px;background:#122b52;color:white}.chat-avatar{display:grid;place-items:center;width:42px;height:42px;flex-shrink:0;background:#facc15;color:#122b52;border-radius:13px;font-weight:900}.chat-heading{flex:1}.chat-heading h2{font-size:16px;font-weight:750;line-height:1.4;margin:0}.chat-heading p{font-size:11px;margin:3px 0 0;color:#c4d4eb}.chat-close{font-size:28px;line-height:1;width:32px;height:32px;border-radius:6px}.chat-intro{padding:10px 20px;background:#f0f5fb;color:#48617e;font-size:12px;border-bottom:1px solid #e4ebf3}.chat-log{flex:1;min-height:0;overflow-y:auto;padding:20px 16px;overscroll-behavior:contain;background:#fafbfd}.chat-message{max-width:92%;margin-bottom:16px}.chat-speaker{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:5px;color:#62738c}.chat-message p{white-space:pre-wrap;overflow-wrap:anywhere;font-size:14px;line-height:1.6;border-radius:3px 14px 14px;padding:12px 14px;background:white;border:1px solid #e2e8f0;margin:0}.chat-user{margin-left:auto}.chat-user .chat-speaker{text-align:right}.chat-user p{background:#1e40af;border-color:#1e40af;color:white;border-radius:14px 3px 14px 14px}.chat-wait{font-size:12px;color:#64748b}.chat-choices{display:flex;gap:8px;flex-wrap:wrap;max-height:145px;overflow-y:auto;padding:12px 16px;border-top:1px solid #edf0f5}.chat-choices button{border:1px solid #cbd9ec;color:#1e40af;border-radius:10px;padding:9px 12px;font-size:12px;font-weight:650;text-align:left}.chat-choices button:hover{background:#eff6ff;border-color:#2563eb}.chat-compose{display:flex;gap:8px;padding:12px 16px;border-top:1px solid #edf0f5}.chat-compose input{min-width:0;flex:1;border:1px solid #cbd5e1;border-radius:9px;font-size:14px;padding:10px}.chat-compose button{background:#1e40af;border-radius:9px;color:white;font-weight:700;font-size:12px;padding:0 13px}.chat-footer{padding:10px 16px 14px;display:flex;justify-content:space-between;gap:10px;align-items:center;font-size:10px;color:#64748b}.chat-footer button{color:#1e40af;text-decoration:underline;white-space:nowrap}.chat-error{padding:10px 16px;color:#991b1b;background:#fef2f2;font-size:12px}.chat-error button{text-decoration:underline}.chat-launcher{position:fixed;z-index:1001;bottom:24px;right:24px;display:flex;gap:10px;align-items:center;border-radius:50px;padding:16px 20px;background:#122b52;color:white;font-weight:700;font-size:14px;box-shadow:0 8px 28px #122b5233}.chat-dot{width:7px;height:7px;background:#facc15;border-radius:50%;margin-left:4px}button:disabled{opacity:.5;cursor:wait}button:focus-visible,input:focus-visible{outline:3px solid #eab308;outline-offset:2px}@media(max-width:480px){.driving-chat{right:12px;bottom:84px;width:calc(100vw - 24px);height:calc(100dvh - 104px)}.chat-launcher{right:12px;bottom:16px;padding:13px 17px}.chat-header{padding:16px}}
</style>
