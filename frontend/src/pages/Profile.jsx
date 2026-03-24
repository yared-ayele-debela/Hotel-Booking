import { useState, useMemo, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useMutation } from '@tanstack/react-query';
import { Pencil, X, Loader2, User, CalendarCheck } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import UserAvatar from '../components/UserAvatar';

export default function Profile() {
  const { user, updateUser } = useAuth();
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [editName, setEditName] = useState('');
  const [editEmail, setEditEmail] = useState('');
  const [editPassword, setEditPassword] = useState('');
  const [editPasswordConfirm, setEditPasswordConfirm] = useState('');
  const [editErrors, setEditErrors] = useState({});
  const [avatarFile, setAvatarFile] = useState(null);
  const [removeAvatar, setRemoveAvatar] = useState(false);

  const avatarPreviewUrl = useMemo(
    () => (avatarFile ? URL.createObjectURL(avatarFile) : null),
    [avatarFile]
  );
  useEffect(() => {
    return () => {
      if (avatarPreviewUrl) URL.revokeObjectURL(avatarPreviewUrl);
    };
  }, [avatarPreviewUrl]);

  const updateProfileMutation = useMutation({
    mutationFn: async (payload) => {
      const res = await api.put('/me', payload);
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to update');
      return res.data;
    },
    onSuccess: (data) => {
      updateUser(data?.data);
      setEditModalOpen(false);
      setEditName('');
      setEditEmail('');
      setEditPassword('');
      setEditPasswordConfirm('');
      setAvatarFile(null);
      setRemoveAvatar(false);
      setEditErrors({});
    },
    onError: (err) => {
      const errors = err.response?.data?.errors;
      setEditErrors(errors && typeof errors === 'object' ? errors : {});
    },
  });

  const openEditModal = () => {
    setEditName(user?.name ?? '');
    setEditEmail(user?.email ?? '');
    setEditPassword('');
    setEditPasswordConfirm('');
    setAvatarFile(null);
    setRemoveAvatar(false);
    setEditErrors({});
    setEditModalOpen(true);
  };

  const handleEditSubmit = (e) => {
    e.preventDefault();
    setEditErrors({});
    if (editPassword.trim() && editPassword !== editPasswordConfirm) {
      setEditErrors({ password: ['Passwords do not match.'] });
      return;
    }
    const useMultipart = Boolean(avatarFile || removeAvatar);
    if (useMultipart) {
      const fd = new FormData();
      fd.append('name', editName.trim());
      fd.append('email', editEmail.trim());
      if (editPassword.trim()) {
        fd.append('password', editPassword);
        fd.append('password_confirmation', editPasswordConfirm);
      }
      if (avatarFile) fd.append('avatar', avatarFile);
      if (removeAvatar) fd.append('remove_avatar', '1');
      updateProfileMutation.mutate(fd);
      return;
    }
    const payload = { name: editName.trim(), email: editEmail.trim() };
    if (editPassword.trim()) {
      payload.password = editPassword;
      payload.password_confirmation = editPasswordConfirm;
    }
    updateProfileMutation.mutate(payload);
  };

  if (!user) {
    return (
      <div className="py-12 sm:py-16">
        <div className="rounded-2xl border border-stone-200 bg-white p-8 sm:p-12 text-center max-w-md mx-auto">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 text-stone-400 mb-4">
            <User className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-semibold text-stone-900 mb-2">Sign in to view your profile</h2>
          <p className="text-stone-600 mb-6">Log in to see your bookings and manage your account.</p>
          <Link
            to="/login"
            className="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition-colors"
          >
            Log in
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="py-6 sm:py-8 lg:py-10">
      <div className="max-w-4xl mx-auto">
        {/* Page header */}
        <div className="mb-8 sm:mb-10">
          <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">My Profile</h1>
          <p className="text-stone-600 mt-1">Manage your account and view booking history</p>
        </div>

        {/* Profile card */}
        <section className="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden mb-8 sm:mb-10">
          <div className="p-6 sm:p-8">
            <div className="flex flex-col sm:flex-row sm:items-center gap-6">
              <div className="flex items-center gap-4 shrink-0">
                <UserAvatar user={user} size={80} className="ring-2 ring-stone-100 shadow-sm" />
                <div className="sm:hidden">
                  <h2 className="text-lg font-semibold text-stone-900">{user.name}</h2>
                  <p className="text-sm text-stone-600 truncate max-w-[200px]">{user.email}</p>
                </div>
              </div>
              <div className="flex-1 min-w-0">
                <div className="hidden sm:block">
                  <h2 className="text-xl font-semibold text-stone-900">{user.name}</h2>
                  <p className="text-stone-600 mt-0.5 flex items-center gap-2">
                    <span className="truncate">{user.email}</span>
                  </p>
                </div>
                <button
                  type="button"
                  onClick={openEditModal}
                  className="mt-4 sm:mt-3 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 text-sm font-medium text-stone-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                  aria-label="Edit profile"
                >
                  <Pencil className="w-4 h-4" />
                  Edit profile
                </button>
              </div>
            </div>
          </div>
        </section>

        {/* Quick link to My Bookings */}
        <section className="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
          <Link
            to="/bookings"
            className="flex items-center gap-4 p-6 hover:bg-amber-50/50 transition-colors"
          >
            <div className="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
              <CalendarCheck className="w-6 h-6 text-amber-700" />
            </div>
            <div className="flex-1 min-w-0">
              <h2 className="text-lg font-semibold text-stone-900">My Bookings</h2>
              <p className="text-sm text-stone-600">View and manage your reservations</p>
            </div>
            <span className="text-amber-600 font-medium text-sm">View all →</span>
          </Link>
        </section>
      </div>

      {/* Edit profile modal */}
      {editModalOpen && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
          role="dialog"
          aria-modal="true"
          aria-labelledby="edit-profile-title"
        >
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div className="sticky top-0 bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between">
              <h3 id="edit-profile-title" className="text-lg font-semibold text-stone-900">
                Edit profile
              </h3>
              <button
                type="button"
                onClick={() => setEditModalOpen(false)}
                className="p-2 rounded-lg hover:bg-stone-100 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500"
                aria-label="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={handleEditSubmit} className="p-6 space-y-5">
              <div className="flex flex-col items-center gap-3 pb-2 border-b border-stone-100">
                <div className="ring-1 ring-stone-200 rounded-full overflow-hidden">
                  {avatarFile && avatarPreviewUrl ? (
                    <img src={avatarPreviewUrl} alt="" className="w-20 h-20 object-cover" width={80} height={80} />
                  ) : removeAvatar ? (
                    <div className="w-20 h-20 bg-stone-100 flex items-center justify-center text-stone-400 text-xs text-center px-2">
                      No photo
                    </div>
                  ) : (
                    <UserAvatar user={user} size={80} />
                  )}
                </div>
                <div className="w-full">
                  <label htmlFor="edit-avatar" className="block text-sm font-medium text-stone-700 mb-1.5">
                    Profile photo
                  </label>
                  <input
                    id="edit-avatar"
                    type="file"
                    accept="image/*"
                    className="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-amber-50 file:text-amber-900"
                    onChange={(e) => {
                      const f = e.target.files?.[0];
                      setAvatarFile(f || null);
                      if (f) setRemoveAvatar(false);
                    }}
                  />
                  {user?.avatar_url && (
                    <label className="mt-2 flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
                      <input
                        type="checkbox"
                        checked={removeAvatar}
                        onChange={(e) => {
                          setRemoveAvatar(e.target.checked);
                          if (e.target.checked) setAvatarFile(null);
                        }}
                      />
                      Remove current photo
                    </label>
                  )}
                  {editErrors.avatar && (
                    <p className="text-sm text-red-600 mt-1">{editErrors.avatar[0]}</p>
                  )}
                </div>
              </div>
              <div>
                <label htmlFor="edit-name" className="block text-sm font-medium text-stone-700 mb-1.5">
                  Name
                </label>
                <input
                  id="edit-name"
                  type="text"
                  value={editName}
                  onChange={(e) => setEditName(e.target.value)}
                  className="w-full rounded-xl border border-stone-300 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow"
                  required
                  maxLength={255}
                />
                {editErrors.name && <p className="text-sm text-red-600 mt-1">{editErrors.name[0]}</p>}
              </div>
              <div>
                <label htmlFor="edit-email" className="block text-sm font-medium text-stone-700 mb-1.5">
                  Email
                </label>
                <input
                  id="edit-email"
                  type="email"
                  value={editEmail}
                  onChange={(e) => setEditEmail(e.target.value)}
                  className="w-full rounded-xl border border-stone-300 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow"
                  required
                />
                {editErrors.email && <p className="text-sm text-red-600 mt-1">{editErrors.email[0]}</p>}
              </div>
              <div>
                <label htmlFor="edit-password" className="block text-sm font-medium text-stone-700 mb-1.5">
                  New password <span className="text-stone-500 font-normal">(leave blank to keep)</span>
                </label>
                <input
                  id="edit-password"
                  type="password"
                  value={editPassword}
                  onChange={(e) => setEditPassword(e.target.value)}
                  className="w-full rounded-xl border border-stone-300 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow"
                  placeholder="••••••••"
                  autoComplete="new-password"
                />
                {editErrors.password && <p className="text-sm text-red-600 mt-1">{editErrors.password[0]}</p>}
              </div>
              {editPassword && (
                <div>
                  <label htmlFor="edit-password-confirm" className="block text-sm font-medium text-stone-700 mb-1.5">
                    Confirm new password
                  </label>
                  <input
                    id="edit-password-confirm"
                    type="password"
                    value={editPasswordConfirm}
                    onChange={(e) => setEditPasswordConfirm(e.target.value)}
                    className="w-full rounded-xl border border-stone-300 px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow"
                    placeholder="••••••••"
                    autoComplete="new-password"
                  />
                </div>
              )}
              {updateProfileMutation.error && !(updateProfileMutation.error?.response?.data?.errors) && (
                <ErrorMessage
                  message={
                    updateProfileMutation.error?.response?.data?.message || updateProfileMutation.error?.message
                  }
                />
              )}
              <div className="flex gap-3 pt-2">
                <button
                  type="submit"
                  disabled={updateProfileMutation.isPending}
                  className="flex-1 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 disabled:opacity-50 transition-colors min-h-[44px]"
                >
                  {updateProfileMutation.isPending ? (
                    <span className="inline-flex items-center gap-2">
                      <Loader2 className="w-4 h-4 animate-spin" />
                      Saving…
                    </span>
                  ) : (
                    'Save changes'
                  )}
                </button>
                <button
                  type="button"
                  onClick={() => setEditModalOpen(false)}
                  className="px-5 py-3 rounded-xl border border-stone-300 text-stone-700 hover:bg-stone-50 transition-colors min-h-[44px]"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
