import { useState, useRef, useEffect } from 'react';
import { MessageCircle, X, Send, Sparkles } from 'lucide-react';
import { api } from '../lib/api';

export default function AiChatWidget() {
  const [open, setOpen] = useState(false);
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const bottomRef = useRef(null);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, open]);

  const send = async (e) => {
    e?.preventDefault();
    const text = input.trim();
    if (!text || loading) return;
    const userMsg = { role: 'user', content: text };
    const toSend = [...messages, userMsg];
    setMessages(toSend);
    setInput('');
    setLoading(true);
    setError(null);
    try {
      const res = await api.post('/ai/chat', { messages: toSend });
      const reply = res.data?.data?.message?.content;
      if (reply) {
        setMessages([...toSend, { role: 'assistant', content: reply }]);
      } else {
        setError(res.data?.message || 'No reply from assistant.');
      }
    } catch (err) {
      setError(err?.response?.data?.message || 'Assistant is unavailable. Set GEMINI_API_KEY on the server.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#1a1a1a] text-white shadow-lg ring-2 ring-[#b8860b]/40 hover:bg-[#2d2a28] transition-colors"
        aria-label="Open AI assistant"
      >
        <Sparkles className="h-6 w-6 text-[#f9edd1]" />
      </button>

      {open && (
        <div
          className="fixed bottom-5 right-5 z-50 flex w-[min(100vw-2rem,400px)] max-h-[min(85vh,520px)] flex-col rounded-2xl border border-[#e8e4dd] bg-white shadow-2xl"
          role="dialog"
          aria-labelledby="ai-chat-title"
        >
          <div className="flex items-center justify-between border-b border-[#e8e4dd] px-4 py-3 bg-[#faf8f5] rounded-t-2xl">
            <div className="flex items-center gap-2">
              <MessageCircle className="h-5 w-5 text-[#b8860b]" />
              <h2 id="ai-chat-title" className="font-semibold text-[#1a1a1a] text-sm">
                Booking assistant
              </h2>
            </div>
            <button
              type="button"
              onClick={() => setOpen(false)}
              className="p-1.5 rounded-lg hover:bg-[#e8e4dd]/80 text-[#5c5852]"
              aria-label="Close"
            >
              <X className="h-5 w-5" />
            </button>
          </div>
          <div className="flex-1 overflow-y-auto px-4 py-3 space-y-3 min-h-[200px] max-h-[340px]">
            {messages.length === 0 && (
              <p className="text-sm text-[#5c5852]">
                Ask about searching hotels, how booking works, or cancellation in general. For live prices, use the
                search on the site.
              </p>
            )}
            {messages.map((m, i) => (
              <div
                key={i}
                className={`text-sm rounded-xl px-3 py-2 max-w-[95%] ${
                  m.role === 'user'
                    ? 'ml-auto bg-[#1a1a1a] text-white'
                    : 'mr-auto bg-[#f5f2ed] text-[#1a1a1a] border border-[#e8e4dd]'
                }`}
              >
                {m.content}
              </div>
            ))}
            {loading && <p className="text-xs text-[#7a756d]">Thinking…</p>}
            {error && <p className="text-xs text-red-600">{error}</p>}
            <div ref={bottomRef} />
          </div>
          <form onSubmit={send} className="border-t border-[#e8e4dd] p-3 flex gap-2">
            <input
              value={input}
              onChange={(e) => setInput(e.target.value)}
              placeholder="Ask a question…"
              className="flex-1 min-w-0 rounded-xl border border-[#e8e4dd] px-3 py-2 text-sm text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b]"
              disabled={loading}
            />
            <button
              type="submit"
              disabled={loading || !input.trim()}
              className="shrink-0 rounded-xl bg-[#b8860b] text-white p-2.5 hover:bg-[#996f09] disabled:opacity-50"
              aria-label="Send"
            >
              <Send className="h-5 w-5" />
            </button>
          </form>
        </div>
      )}
    </>
  );
}
