export default function ErrorMessage({ message, code, onRetry }) {
  return (
    <div
      className="rounded-xl border border-[#e5c261]/60 bg-[#f9edd1]/50 p-4 text-[#1a1a1a]"
      role="alert"
      aria-live="polite"
    >
      <p className="font-medium">{message}</p>
      {code && <p className="text-sm mt-1 opacity-80">Code: {code}</p>}
      {onRetry && (
        <button
          type="button"
          onClick={onRetry}
          className="mt-3 px-4 py-2 rounded-xl bg-[#1a1a1a] text-white font-semibold hover:bg-[#2d2a28] transition-colors"
        >
          Try again
        </button>
      )}
    </div>
  );
}
