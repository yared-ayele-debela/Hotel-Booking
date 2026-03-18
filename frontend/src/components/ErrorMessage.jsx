export default function ErrorMessage({ message, code, onRetry }) {
  return (
    <div
      className="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900"
      role="alert"
      aria-live="polite"
    >
      <p className="font-medium">{message}</p>
      {code && <p className="text-sm mt-1 opacity-80">Code: {code}</p>}
      {onRetry && (
        <button
          type="button"
          onClick={onRetry}
          className="mt-3 px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors"
        >
          Try again
        </button>
      )}
    </div>
  );
}
