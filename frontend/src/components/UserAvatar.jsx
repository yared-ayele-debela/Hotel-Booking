/**
 * Customer (or any) user thumbnail: photo from avatar_url or initials.
 */
export default function UserAvatar({
  user,
  size = 40,
  className = '',
  fallbackClassName = 'bg-[#e8e4dd] text-[#1a1a1a]',
}) {
  const initial =
    user?.name?.trim()?.charAt(0)?.toUpperCase() ||
    user?.email?.trim()?.charAt(0)?.toUpperCase() ||
    '?';
  const style = { width: size, height: size, fontSize: Math.max(12, Math.round(size * 0.38)) };

  if (user?.avatar_url) {
    return (
      <img
        src={user.avatar_url}
        alt=""
        className={`rounded-full object-cover shrink-0 ${className}`}
        width={size}
        height={size}
        style={style}
      />
    );
  }

  return (
    <span
      className={`rounded-full flex items-center justify-center font-semibold shrink-0 ${fallbackClassName} ${className}`}
      style={style}
      aria-hidden
    >
      {initial}
    </span>
  );
}
