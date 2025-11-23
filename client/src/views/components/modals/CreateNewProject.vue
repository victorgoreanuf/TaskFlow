<template>
  <transition
      name="modal"
      @enter="enter"
      @leave="leave"
      :css="false"
  >
    <div v-if="value" class="modal-wrapper" @click.self="close">
      <div
          class="modal-content"
          @click.stop
          ref="modalContent"
      >
        <!-- Close Button (X) -->
        <button
            type="button"
            class="modal-close-icon"
            @click="close"
            aria-label="Close modal"
        >
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Header -->
        <h2 class="modal-header">{{ title }}</h2>

        <!-- Main content -->
        <div class="modal-body">
          <slot></slot>
        </div>

        <!-- Footer with Save button -->
        <div class="modal-footer">
          <button type="button" class="btn-primary" @click="$emit('save')">
            Save
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    value: {      // v-model support in Vue 2
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      default: 'Modal Title'
    }
  },

  methods: {
    close() {
      this.$emit('input', false)   // Vue 2 v-model uses "input" event
    },

    // Manual animation with Velocity.js (lightweight) or plain CSS
    // Here we use simple CSS classes – no extra library needed
    enter(el, done) {
      el.offsetHeight // trigger reflow
      el.style.opacity = 0
      el.style.transform = 'scale(0.95)'
      this.$nextTick(() => {
        el.style.transition = 'all 300ms cubic-bezier(0.4, 0, 0.2, 1)'
        el.style.opacity = 1
        el.style.transform = 'scale(1)'
        setTimeout(done, 300)
      })
    },
    leave(el, done) {
      el.style.opacity = 1
      el.style.transform = 'scale(1)'
      el.style.transition = 'all 250ms ease-in'
      this.$nextTick(() => {
        el.style.opacity = 0
        el.style.transform = 'scale(0.95)'
        setTimeout(done, 250)
      })
    }
  }
}
</script>

<style scoped>
/* Full-screen wrapper – transparent, only centers the modal */
.modal-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  overflow: auto;
}

/* Click outside to close */
.modal-wrapper::before {
  content: '';
  position: absolute;
  inset: 0;
}

/* The actual modal – pure white, fully opaque */
.modal-content {
  position: relative;
  background: #ffffff;           /* Pure white */
  border-radius: 1rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  padding: 2rem;
  width: 100%;
  max-width: 36rem;
  opacity: 1;
}

/* Header */
.modal-header {
  font-size: 1.875rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 1.5rem 0;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

/* Body */
.modal-body {
  margin-bottom: 2rem;
  color: #374151;
}

/* Close icon */
.modal-close-icon {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  padding: 0.5rem;
  border-radius: 50%;
  color: #6b7280;
  cursor: pointer;
  transition: all 150ms;
}
.modal-close-icon:hover {
  background: #f3f4f6;
  color: #111827;
}
.modal-close-icon svg {
  width: 1.5rem;
  height: 1.5rem;
  display: block;
}

/* Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

/* Primary button */
.btn-primary {
  padding: 0.65rem 1.75rem;
  font-weight: 600;
  color: white;
  background: #4f46e5;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background 150ms;
}
.btn-primary:hover {
  background: #4338ca;
}
</style>