<template>
  <transition
      name="modal-anim"
      @enter="enter"
      @leave="leave"
      :css="false"
  >
    <div v-if="value" class="modal-backdrop" @click.self="close">
      <div
          class="modal-card"
          @click.stop
          ref="modalContent"
      >
        <button
            type="button"
            class="modal-close-btn"
            @click="close"
            aria-label="Close modal"
        >
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div class="modal-header">
          <h2>{{ title }}</h2>
        </div>

        <div class="modal-body">
          <slot>
            <p class="placeholder-text">Modal content goes here...</p>
          </slot>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-primary" @click="$emit('save')">
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    value: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      default: 'Confirm Action'
    }
  },

  methods: {
    close() {
      this.$emit('input', false)
    },

    // --- Animations ---
    enter(el, done) {
      // animate the backdrop opacity
      el.style.opacity = 0

      // animate the card inside (we find it via querySelector for better control)
      const card = el.querySelector('.modal-card')
      card.style.opacity = 0
      card.style.transform = 'translateY(20px) scale(0.95)'

      // Trigger reflow
      el.offsetHeight

      // Animate in
      this.$nextTick(() => {
        // Backdrop fade
        el.style.transition = 'opacity 300ms ease-out'
        el.style.opacity = 1

        // Card pop-in (springy bezier)
        card.style.transition = 'all 400ms cubic-bezier(0.34, 1.56, 0.64, 1)'
        card.style.opacity = 1
        card.style.transform = 'translateY(0) scale(1)'

        setTimeout(done, 400)
      })
    },

    leave(el, done) {
      const card = el.querySelector('.modal-card')

      // Animate out
      el.style.transition = 'opacity 300ms ease'
      card.style.transition = 'all 300ms ease-in'

      this.$nextTick(() => {
        el.style.opacity = 0
        card.style.opacity = 0
        card.style.transform = 'translateY(20px) scale(0.95)'
        setTimeout(done, 300)
      })
    }
  }
}
</script>

<style scoped>
/* 1. The Backdrop: Dark, blurred, fixed */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999;
  /* Dark overlay with blur */
  background-color: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

/* 2. The Modal Card: Clean, modern shadow, rounded */
.modal-card {
  position: relative;
  background: #ffffff;
  border-radius: 1.5rem; /* Larger border radius */
  box-shadow:
      0 10px 15px -3px rgba(0, 0, 0, 0.1),
      0 4px 6px -2px rgba(0, 0, 0, 0.05),
      0 25px 50px -12px rgba(0, 0, 0, 0.25); /* Deep shadow */
  width: 100%;
  max-width: 32rem;
  display: flex;
  flex-direction: column;
  max-height: 90vh; /* Prevent overflow on small screens */
  border: 1px solid rgba(255, 255, 255, 0.5); /* Subtle border for definition */
}

/* 3. Header: Clean, no harsh lines */
.modal-header {
  padding: 2rem 2rem 0.5rem 2rem;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  letter-spacing: -0.025em;
  line-height: 1.2;
}

/* 4. Body: Comfortable reading */
.modal-body {
  padding: 1rem 2rem 2rem 2rem;
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.6;
  overflow-y: auto;
}

/* Placeholder styling in case slot is empty */
.placeholder-text {
  font-style: italic;
  color: #9ca3af;
}

/* 5. Footer: Right aligned, spaced */
.modal-footer {
  padding: 1.5rem 2rem;
  background-color: #f9fafb; /* Slight contrast for footer */
  border-bottom-left-radius: 1.5rem;
  border-bottom-right-radius: 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

/* 6. Close Icon: Floating, subtle */
.modal-close-btn {
  position: absolute;
  top: 1.25rem;
  right: 1.25rem;
  background: transparent;
  border: none;
  border-radius: 50%;
  padding: 0.5rem;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close-btn:hover {
  background-color: #f3f4f6;
  color: #ef4444; /* Red on hover */
  transform: rotate(90deg); /* Playful rotation */
}

.modal-close-btn svg {
  width: 1.25rem;
  height: 1.25rem;
}

/* 7. Primary Button: Gradient & Glow */
.btn-primary {
  padding: 0.75rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
  transition: all 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
}

.btn-primary:active {
  transform: translateY(1px);
  box-shadow: 0 2px 4px -1px rgba(79, 70, 229, 0.3);
}


</style>