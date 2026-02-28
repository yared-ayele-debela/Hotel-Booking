import React, { createContext, useContext, useState, useCallback } from 'react';
import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-react';
import { cn } from '../../lib/utils';

const ToastContext = createContext(undefined);

export const useToast = () => {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }
  return context;
};

const toastVariants = {
  success: {
    className: 'bg-green-50 border-green-200 text-green-800',
    icon: CheckCircle,
    iconClassName: 'text-green-600',
  },
  error: {
    className: 'bg-red-50 border-red-200 text-red-800',
    icon: AlertCircle,
    iconClassName: 'text-red-600',
  },
  warning: {
    className: 'bg-amber-50 border-amber-200 text-amber-800',
    icon: AlertTriangle,
    iconClassName: 'text-amber-600',
  },
  info: {
    className: 'bg-cyan-50 border-cyan-200 text-cyan-800',
    icon: Info,
    iconClassName: 'text-cyan-600',
  },
};

export const ToastProvider = ({ children }) => {
  const [toasts, setToasts] = useState([]);

  const toast = useCallback(({ id, title, description, variant = 'info', duration = 5000 }) => {
    const newToast = {
      id: id || Date.now(),
      title,
      description,
      variant,
    };

    setToasts((prev) => [...prev, newToast]);

    if (duration > 0) {
      setTimeout(() => {
        setToasts((prev) => prev.filter((t) => t.id !== newToast.id));
      }, duration);
    }

    return newToast.id;
  }, []);

  const dismiss = useCallback((toastId) => {
    setToasts((prev) => prev.filter((toast) => toast.id !== toastId));
  }, []);

  return (
    <ToastContext.Provider value={{ toast, dismiss }}>
      {children}
      <ToastContainer toasts={toasts} onDismiss={dismiss} />
    </ToastContext.Provider>
  );
};

const ToastContainer = ({ toasts, onDismiss }) => {
  return (
    <div className="fixed top-4 right-4 z-50 space-y-2">
      {toasts.map((toast) => (
        <Toast key={toast.id} toast={toast} onDismiss={() => onDismiss(toast.id)} />
      ))}
    </div>
  );
};

const Toast = ({ toast, onDismiss }) => {
  const { className, icon: Icon, iconClassName } = toastVariants[toast.variant];

  return (
    <div
      className={cn(
        'group relative flex w-full max-w-sm items-center justify-between space-x-4 overflow-hidden rounded-lg border p-4 shadow-lg transition-all duration-300 ease-in-out',
        className
      )}
    >
      <div className="flex items-center space-x-3">
        <Icon className={cn('h-5 w-5 flex-shrink-0', iconClassName)} />
        <div className="flex-1">
          {toast.title && <p className="font-medium">{toast.title}</p>}
          {toast.description && <p className="text-sm opacity-90">{toast.description}</p>}
        </div>
      </div>
      <button
        onClick={onDismiss}
        className="absolute right-2 top-2 rounded-lg opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
      >
        <X className="h-4 w-4" />
        <span className="sr-only">Close</span>
      </button>
    </div>
  );
};

export const toast = {
  success: (props) => {
    const { toast } = useToast();
    return toast({ ...props, variant: 'success' });
  },
  error: (props) => {
    const { toast } = useToast();
    return toast({ ...props, variant: 'error' });
  },
  warning: (props) => {
    const { toast } = useToast();
    return toast({ ...props, variant: 'warning' });
  },
  info: (props) => {
    const { toast } = useToast();
    return toast({ ...props, variant: 'info' });
  },
};
