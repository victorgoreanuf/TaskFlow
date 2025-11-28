<template>
  <div class="infinite-viewport">

    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted font-weight-bold">Loading Board...</p>
    </div>

    <div v-else-if="!displayProject" class="empty-state d-flex justify-content-center align-items-center h-100">
      <div class="alert alert-warning">No project data loaded.</div>
    </div>

    <div
        v-else
        ref="canvas"
        class="infinite-canvas"
        :style="canvasStyle"
        @mousedown="startPan"
        @mousemove="handlePan"
        @mouseup="endPan"
        @mouseleave="endPan"
        @wheel.prevent="handleWheel"
    >
      <div class="board-content d-inline-flex align-items-start p-5">

        <div
            v-for="column in displayProject.columns"
            :key="column.id"
            class="board-column mr-4"
            @mousedown.stop
        >
          <div class="kanban-column h-100 d-flex flex-column rounded border-0 shadow-sm" :class="column.color || 'theme-gray'">

            <div class="column-header py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
              <span class="font-weight-bold mx-auto">{{ column.title }}</span>
              <button class="btn btn-link settings-icon-btn p-0" @click="openEditColumnModal(column)">
                <span class="gear-icon">&#9881;</span>
              </button>
            </div>

            <div class="column-body flex-grow-1 px-2 pb-3 pt-3">

              <draggable
                  v-model="column.tasks"
                  group="kanban-tasks"
                  ghost-class="ghost-card"
                  class="draggable-area"
                  @change="(event) => handleTaskChange(event, column.id)"
              >
                <div
                    v-for="task in column.tasks"
                    :key="task.id"
                    class="task-card card mb-3 border-0 shadow-sm"
                    @click="openEditTaskModal(task)"
                >
                  <div class="card-body p-3">
                    <h6 class="card-title font-weight-bold mb-2 text-dark">{{ task.name }}</h6>

                    <p class="text-muted small mb-3 description-clamp" v-if="task.description">
                      {{ task.description }}
                    </p>

                    <div v-if="task.due_date" class="task-meta border-top border-light pt-2 d-flex align-items-center text-muted">
                      <span class="small font-weight-bold mr-1">DUE:</span>
                      <span class="small" :class="{'text-danger': isOverdue(task.due_date)}">
                                            {{ formatDate(task.due_date) }}
                                        </span>
                    </div>
                  </div>
                </div>
              </draggable>
              <button @click="openCreateTaskModal(column.id)" class="btn btn-block dashed-btn mt-1 text-muted font-weight-bold py-2">
                + Add Task
              </button>
            </div>
          </div>
        </div>

        <div class="board-column" @mousedown.stop>
          <button @click="openCreateColumnModal" class="btn btn-light h-100 w-100 d-flex flex-column justify-content-center align-items-center border-0 shadow-sm new-column-btn">
            <span class="h1 mb-0 text-muted" style="font-weight: 200;">+</span>
            <span class="font-weight-bold text-muted mt-2">New Column</span>
          </button>
        </div>
      </div>
    </div>

    <div class="canvas-controls">
      <div class="project-title mb-2">
        <h5 class="font-weight-bold m-0">{{ displayProject ? displayProject.name : 'Loading...' }}</h5>
      </div>
      <div class="btn-group shadow-sm bg-white rounded-pill">
        <button class="btn btn-icon" @click="zoomOut">-</button>
        <span class="zoom-level">{{ Math.round(scale * 100) }}%</span>
        <button class="btn btn-icon" @click="zoomIn">+</button>
        <button class="btn btn-icon border-left" @click="resetView">Fit</button>
      </div>
    </div>

    <div v-if="modals.task.show">
      <div class="modal-backdrop fade show"></div>
      <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content clean-modal border-0 shadow-lg">
            <div class="modal-header border-0 px-4 py-4 d-flex align-items-center justify-content-between bg-white rounded-top">
              <h5 class="modal-title font-weight-bold text-dark h4 mb-0">
                {{ modals.task.isEditing ? 'Edit Task' : 'New Task' }}
              </h5>
              <button type="button" class="btn btn-sm btn-light rounded-sm close-custom-btn d-flex align-items-center justify-content-center" @click="closeModals">
                <span aria-hidden="true" style="font-size: 1.2rem; line-height: 1;">&times;</span>
              </button>
            </div>
            <div class="modal-body px-4 pb-4 pt-0 bg-white">
              <form @submit.prevent="submitTask">
                <div class="form-group mb-4">
                  <label class="clean-label">Task Name</label>
                  <input v-model="modals.task.form.name" type="text" class="form-control clean-input" placeholder="e.g. Website Redesign" required autofocus>
                </div>
                <div class="form-group mb-4">
                  <label class="clean-label">Description / Bio</label>
                  <textarea v-model="modals.task.form.description" class="form-control clean-input" rows="4" placeholder="Add details here..."></textarea>
                </div>
                <div class="form-group mb-5">
                  <label class="clean-label">Due Date</label>
                  <input v-model="modals.task.form.due_date" type="date" class="form-control clean-input">
                </div>
                <div class="d-flex align-items-center justify-content-between">
                  <button v-if="modals.task.isEditing" type="button" class="btn btn-link text-danger p-0 font-weight-bold small" style="text-decoration: none;" @click="deleteTask">Delete Task</button>
                  <span v-else></span>
                  <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-text text-secondary font-weight-bold mr-4" @click="closeModals">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-lg px-5 rounded-pill shadow-sm font-weight-bold" :disabled="loadingAction">
                      {{ loadingAction ? 'Saving...' : (modals.task.isEditing ? 'Update' : 'Create') }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="modals.column.show">
      <div class="modal-backdrop fade show"></div>
      <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content clean-modal border-0 shadow-lg">
            <div class="modal-header border-0 px-4 py-4 d-flex align-items-center justify-content-between bg-white rounded-top">
              <h5 class="modal-title font-weight-bold text-dark h5 mb-0">
                {{ modals.column.isEditing ? 'Edit Column' : 'New Column' }}
              </h5>
              <button type="button" class="btn btn-sm btn-light rounded-sm close-custom-btn d-flex align-items-center justify-content-center" @click="closeModals">
                <span aria-hidden="true" style="font-size: 1.2rem; line-height: 1;">&times;</span>
              </button>
            </div>
            <div class="modal-body px-4 pb-4 pt-0 bg-white">
              <form @submit.prevent="submitColumn">
                <div class="form-group mb-4">
                  <label class="clean-label">Column Title</label>
                  <input v-model="modals.column.form.title" type="text" class="form-control clean-input" placeholder="e.g. In QA" required autofocus>
                </div>
                <div class="form-group mb-4">
                  <label class="clean-label">Theme Color</label>
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <div v-for="theme in availableThemes" :key="theme.class" @click="modals.column.form.color = theme.class" class="color-swatch" :class="{ 'selected': modals.column.form.color === theme.class }" :style="{ backgroundColor: theme.hex }" :title="theme.name"></div>
                  </div>
                </div>
                <div class="d-flex align-items-center mt-4 justify-content-between">
                  <button v-if="modals.column.isEditing" type="button" class="btn btn-link text-danger p-0 font-weight-bold small" style="text-decoration: none;" @click="deleteColumn">Delete</button>
                  <span v-else></span>
                  <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-dark px-4 rounded-pill font-weight-bold shadow-sm" :disabled="loadingAction">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Vue from 'vue';
import draggable from 'vuedraggable'; // <--- 1. Import Draggable

export default {
  name: 'KanbanBoard',
  components: {
    draggable // <--- 2. Register Component
  },
  props: {
    id: { type: [String, Number], required: true }
  },
  data() {
    return {
      loading: false,
      loadingAction: false,

      scale: 1, panning: false, pointX: 50, pointY: 50, startX: 0, startY: 0,

      availableThemes: [
        { name: 'Gray',   class: 'theme-gray',   hex: '#e9ecef' },
        { name: 'Pink',   class: 'theme-pink',   hex: '#ffeae8' },
        { name: 'Yellow', class: 'theme-yellow', hex: '#fff9db' },
        { name: 'Blue',   class: 'theme-blue',   hex: '#e8f0fe' },
        { name: 'Purple', class: 'theme-purple', hex: '#e9e5ff' },
        { name: 'Green',  class: 'theme-green',  hex: '#e6f4ea' }
      ],

      modals: {
        task: {
          show: false,
          isEditing: false,
          editingId: null,
          form: { columnId: null, name: '', description: '', due_date: '' }
        },
        column: {
          show: false,
          isEditing: false,
          editingId: null,
          form: { title: '', color: 'theme-gray' }
        }
      },
      mockData: { id: 0, name: "Loading...", columns: [] }
    };
  },
  computed: {
    ...mapGetters('project', ['currentProject']),
    displayProject() {
      if (this.currentProject && this.currentProject.columns) return this.currentProject;
      return this.mockData;
    },
    canvasStyle() {
      return { transform: `translate(${this.pointX}px, ${this.pointY}px) scale(${this.scale})` };
    }
  },
  watch: {
    id: { immediate: true, handler(newSlug) { this.initializeBoard(newSlug); } }
  },
  methods: {
    async initializeBoard(slug) {
      if (!slug) return;
      this.loading = true;
      try {
        await this.$store.dispatch('project/fetchProjectBySlug', slug);
        this.resetView();
      } catch (e) { console.warn("Load failed"); }
      finally { this.loading = false; }
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    },

    isOverdue(dateStr) {
      if (!dateStr) return false;
      return new Date(dateStr).setHours(0,0,0,0) < new Date().setHours(0,0,0,0);
    },

    // --- DRAG AND DROP HANDLER ---
    handleTaskChange(evt, columnId) {
      // evt contains 'added', 'removed', or 'moved'
      // We only care about 'added' (moved from another col) or 'moved' (reordered in same col)

      let eventType = null;
      let item = null;
      let newIndex = null;

      if (evt.added) {
        eventType = 'added';
        item = evt.added.element;
        newIndex = evt.added.newIndex;
      } else if (evt.moved) {
        eventType = 'moved';
        item = evt.moved.element;
        newIndex = evt.moved.newIndex;
      }

      if (eventType) {
        // Backend expects 1-based order
        const newOrder = newIndex + 1;

        this.$store.dispatch('project/moveTask', {
          projectSlug: this.id,
          taskId: item.id,
          columnId: columnId,
          order: newOrder
        });
      }
    },

    // --- CANVAS METHODS ---
    startPan(e) { if (e.button !== 0 && e.button !== 1) return; this.panning = true; this.startX = e.clientX - this.pointX; this.startY = e.clientY - this.pointY; this.$refs.canvas.style.cursor = 'grabbing'; },
    handlePan(e) { if (!this.panning) return; e.preventDefault(); this.pointX = e.clientX - this.startX; this.pointY = e.clientY - this.startY; },
    endPan() { this.panning = false; if(this.$refs.canvas) this.$refs.canvas.style.cursor = 'grab'; },
    handleWheel(e) { const zoomIntensity = 0.1; const direction = e.deltaY > 0 ? -1 : 1; this.scale = Math.min(Math.max(0.2, this.scale + (direction * zoomIntensity)), 3); },
    zoomIn() { this.scale = Math.min(this.scale + 0.2, 3); },
    zoomOut() { this.scale = Math.max(this.scale - 0.2, 0.2); },
    resetView() { this.scale = 1; this.pointX = 50; this.pointY = 50; },

    // --- MODAL METHODS ---
    closeModals() { this.modals.task.show = false; this.modals.column.show = false; },

    // TASK CRUD
    openCreateTaskModal(colId) {
      this.modals.task.isEditing = false;
      this.modals.task.editingId = null;
      this.modals.task.form = { columnId: colId, name: '', description: '', due_date: '' };
      this.modals.task.show = true;
    },

    openEditTaskModal(task) {
      this.modals.task.isEditing = true;
      this.modals.task.editingId = task.id;
      let formattedDate = '';
      if(task.due_date) { formattedDate = new Date(task.due_date).toISOString().split('T')[0]; }
      this.modals.task.form = { columnId: task.column_id, name: task.name, description: task.description, due_date: formattedDate };
      this.modals.task.show = true;
    },

    async submitTask() {
      if (!this.modals.task.form.name) return;
      this.loadingAction = true;
      try {
        if (this.modals.task.isEditing) {
          await this.$store.dispatch('project/updateTask', { projectSlug: this.id, taskId: this.modals.task.editingId, ...this.modals.task.form });
        } else {
          await this.$store.dispatch('project/createTask', { projectSlug: this.id, ...this.modals.task.form });
        }
        await this.$store.dispatch('project/fetchProjectBySlug', this.id);
        this.closeModals();
      } catch (e) { console.error(e); }
      finally { this.loadingAction = false; }
    },

    async deleteTask() {
      if(!confirm("Are you sure?")) return;
      this.loadingAction = true;
      try {
        await this.$store.dispatch('project/deleteTask', { projectSlug: this.id, taskId: this.modals.task.editingId });
        await this.$store.dispatch('project/fetchProjectBySlug', this.id);
        this.closeModals();
      } catch(e) { console.error(e); }
      finally { this.loadingAction = false; }
    },

    // COLUMN CRUD
    openCreateColumnModal() {
      this.modals.column.isEditing = false;
      this.modals.column.form.title = '';
      const randomTheme = this.availableThemes[Math.floor(Math.random() * this.availableThemes.length)];
      this.modals.column.form.color = randomTheme.class;
      this.modals.column.show = true;
    },

    openEditColumnModal(column) {
      this.modals.column.isEditing = true;
      this.modals.column.editingId = column.id;
      this.modals.column.form.title = column.title;
      this.modals.column.form.color = column.color || 'theme-gray';
      this.modals.column.show = true;
    },

    async submitColumn() {
      if (!this.modals.column.form.title) return;
      this.loadingAction = true;
      try {
        if (this.modals.column.isEditing) {
          await this.$store.dispatch('project/updateColumn', { projectSlug: this.id, columnId: this.modals.column.editingId, ...this.modals.column.form });
        } else {
          await this.$store.dispatch('project/createColumn', { projectSlug: this.id, ...this.modals.column.form });
        }
        await this.$store.dispatch('project/fetchProjectBySlug', this.id);
        this.closeModals();
      } catch (e) { console.error(e); } finally { this.loadingAction = false; }
    },

    async deleteColumn() {
      if(!confirm("Are you sure?")) return;
      this.loadingAction = true;
      try {
        await this.$store.dispatch('project/deleteColumn', { projectSlug: this.id, columnId: this.modals.column.editingId });
        await this.$store.dispatch('project/fetchProjectBySlug', this.id);
        this.closeModals();
      } catch (e) { console.error(e); } finally { this.loadingAction = false; }
    }
  }
}
</script>

<style scoped>
/* =================================
   1. CANVAS
   ================================= */
.infinite-viewport {
  position: relative;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  background-color: #f4f5f7;
  background-image: radial-gradient(#dfe1e6 1px, transparent 1px);
  background-size: 24px 24px;
}

.infinite-canvas {
  transform-origin: 0 0;
  width: 100%;
  height: 100%;
  cursor: grab;
  will-change: transform;
}
.infinite-canvas:active { cursor: grabbing; }

/* =================================
   2. BOARD ITEMS
   ================================= */
.board-column { width: 300px; flex-shrink: 0; }
.kanban-column { transition: box-shadow 0.3s ease; }
.kanban-column:hover { box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important; }

/* THEME COLORS */
.theme-pink .column-header { background-color: #ffeae8; color: #5c1e1e; border-bottom: 3px solid #fecaca !important; }
.theme-pink { background-color: #fff5f5; }
.theme-yellow .column-header { background-color: #fff9db; color: #665c1e; border-bottom: 3px solid #ffec99 !important; }
.theme-yellow { background-color: #fffceb; }
.theme-blue .column-header { background-color: #e8f0fe; color: #1a3e72; border-bottom: 3px solid #bad6fc !important; }
.theme-blue { background-color: #f4f8ff; }
.theme-purple .column-header { background-color: #e9e5ff; color: #4338ca; border-bottom: 3px solid #d8b4fe !important; }
.theme-purple { background-color: #f3f0ff; }
.theme-green .column-header { background-color: #e6f4ea; color: #134e28; border-bottom: 3px solid #ceead6 !important; }
.theme-green { background-color: #f1f8f4; }
.theme-gray .column-header { background-color: #f8f9fa; color: #495057; border-bottom: 3px solid #ced4da !important; }
.theme-gray { background-color: #f8f9fa; }

/* CARD & BUTTONS */
.task-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1)!important; cursor: pointer; }
.dashed-btn { border: 2px dashed rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); transition: all 0.2s; }
.dashed-btn:hover { background: rgba(255,255,255,0.8); border-color: rgba(0,0,0,0.3); }
.new-column-btn { background-color: rgba(255,255,255,0.6); transition: all 0.2s; height: 300px; }
.new-column-btn:hover { background-color: #fff; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05) !important; transform: translateY(-2px); }
.settings-icon-btn { width: 32px; height: 32px; border-radius: 50%; transition: background-color 0.2s; }
.settings-icon-btn:hover { background-color: rgba(0,0,0,0.05); }
.gear-icon { font-size: 1.35rem; color: #888; line-height: 1; margin-top: -2px; }
.settings-icon-btn:hover .gear-icon { color: #333; }
.description-clamp { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }

/* DRAGGABLE SPECIFIC */
.draggable-area {
  min-height: 50px; /* Ensure you can drop into an empty column */
  display: block;
}
.ghost-card {
  opacity: 0.5;
  background: #e2e8f0;
  border: 2px dashed #cbd5e0;
}

/* =================================
   3. HUD & MODALS
   ================================= */
.canvas-controls { position: fixed; bottom: 30px; right: 30px; z-index: 100; display: flex; flex-direction: column; align-items: flex-end; }
.btn-icon { width: 40px; height: 40px; background: white; border: none; font-weight: bold; color: #555; font-size: 1.2rem; transition: background 0.2s; }
.btn-icon:hover { background: #f0f0f0; }
.zoom-level { display: inline-flex; align-items: center; justify-content: center; width: 60px; font-size: 0.9rem; font-weight: 600; color: #333; background: white; }

.modal-backdrop { background-color: #091e42; opacity: 0.5; z-index: 1040; }
.modal { z-index: 1050; }
.clean-modal { border-radius: 12px; overflow: hidden; }
.close-custom-btn { width: 32px; height: 32px; background-color: #fff; border: 1px solid #eee; color: #555; }
.close-custom-btn:hover { background-color: #f0f0f0; }
.clean-label { display: block; margin-bottom: 8px; font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
.clean-input { background-color: #f5f6f8; border: 1px solid transparent; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #333; transition: all 0.2s; }
.clean-input:focus { background-color: #fff; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); outline: none; }
input[type="date"].clean-input { line-height: 1.5; }
.btn-primary-custom { background-color: #6366f1; border-color: #6366f1; color: white; }
.btn-primary-custom:hover { background-color: #4f46e5; border-color: #4f46e5; transform: translateY(-1px); }
.btn-text { background: none; border: none; padding: 0; }
.btn-text:hover { color: #111 !important; }

/* Color Swatches */
.color-swatch { width: 30px; height: 30px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
.color-swatch:hover { transform: scale(1.1); }
.color-swatch.selected { border-color: #333; transform: scale(1.1); box-shadow: 0 0 0 2px rgba(0,0,0,0.1); }

/* Add this to your style section */

.task-card,
.column-header,
.dashed-btn {
  /* Prevents text selection on draggable items */
  user-select: none;
  -webkit-user-select: none; /* Safari */
  -moz-user-select: none;    /* Firefox */
  -ms-user-select: none;     /* IE10+/Edge */
}

/* Optional: If you want to disable it on the whole board while dragging */
.infinite-canvas:active {
  user-select: none;
}
</style>