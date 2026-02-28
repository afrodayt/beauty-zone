const listeners = new Map();

function on(event, handler) {
  if (!listeners.has(event)) {
    listeners.set(event, new Set());
  }

  listeners.get(event).add(handler);
}

function once(event, handler) {
  const onceHandler = (...args) => {
    off(event, onceHandler);
    handler(...args);
  };

  on(event, onceHandler);
}

function off(event, handler) {
  if (!listeners.has(event)) {
    return;
  }

  listeners.get(event).delete(handler);

  if (listeners.get(event).size === 0) {
    listeners.delete(event);
  }
}

function emit(event, ...args) {
  if (!listeners.has(event)) {
    return;
  }

  for (const handler of listeners.get(event)) {
    handler(...args);
  }
}

export const eventBus = {
  $on: on,
  $once: once,
  $off: off,
  $emit: emit,
};

export const events = {
  SHOW_CLIENT_CREATE_MODAL: "SHOW_CLIENT_CREATE_MODAL",
  SHOW_CLIENT_EDIT_MODAL: "SHOW_CLIENT_EDIT_MODAL",
};
