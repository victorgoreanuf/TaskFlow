<template>
  <div class="kanban-board-view p-6 bg-gray-50 min-h-screen">

    <!-- Loading State / Error State -->
    <div v-if="loading" class="text-center py-20">
      <div class="spinner"></div>
      <p class="text-indigo-600 mt-4">Loading board data...</p>
    </div>

    <!-- Main Content -->
    <div v-else-if="currentProject">

      <!-- Header -->
      <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h1 class="text-3xl font-extrabold text-gray-900">
          Project Board: {{ currentProject.name }}
        </h1>
        <button
            @click="openTaskModal"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out"
        >
          + Add New Task
        </button>
      </div>

      <!-- Board Area -->
      <div class="board-container flex space-x-4 overflow-x-auto pb-4">

        <!-- Kanban Columns (Lists) -->
        <div
            v-for="column in currentProject.columns" :key="column.id"
            class="kanban-list w-80 flex-shrink-0 bg-white border border-gray-200 rounded-xl shadow-lg p-4"
        >
          <h3 class="font-bold text-lg mb-4 text-gray-800 border-b pb-2">
            {{ column.title }}
            <span class="text-sm font-normal text-gray-500 ml-2">({{ column.tasks ? column.tasks.length : 0 }} Tasks)</span>
          </h3>

          <!-- Cards (Tasks) -->
          <div class="space-y-3">
            <div
                v-for="task in column.tasks" :key="task.id"
                class="task-card bg-white p-3 border border-gray-100 rounded-lg shadow-sm hover:shadow-md cursor-pointer transition duration-150"
            >
              <p class="font-medium text-sm text-gray-700">{{ task.name }}</p>
              <div class="text-xs text-gray-400 mt-1">Order: {{ task.order }}</div>
            </div>
          </div>

          <!-- Add Task Button at the bottom of the column -->
          <button class="w-full mt-4 text-indigo-500 hover:text-indigo-700 text-sm py-2">
            + Add Task to {{ column.title }}
          </button>
        </div>

        <!-- Add Column Button -->
        <div class="w-80 flex-shrink-0">
          <button class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-3 px-4 rounded-xl transition duration-150">
            + Add New Column
          </button>
        </div>

      </div>

    </div>

    <!-- Project Not Found / Initial Load Error -->
    <div v-else class="text-center py-20 bg-white rounded-xl shadow-lg p-10">
      <h2 class="text-2xl font-bold text-red-500 mb-4">Project Not Loaded</h2>
      <p class="text-gray-600">
        Could not load project with slug: **{{ id }}**. Please check the URL or try again from the projects list.
      </p>
      <p class="mt-4">
        <router-link to="/" class="text-indigo-600 hover:underline font-medium">
          Go back to Projects List
        </router-link>
      </p>
    </div>
    <div class="mt-4 text-xs text-gray-400">Current Route Slug: {{ id }}</div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'KanbanBoard',

  // The 'id' prop is actually the project slug because of how the routes are defined
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },

  data() {
    return {
      loading: false,
    };
  },

  computed: {
    // 1. Get the current project object from Vuex
    ...mapGetters('project', [
      'currentProject'
    ]),
  },

  watch: {
    // 2. Watch the route ID (slug). If the user navigates, refetch the board data.
    id: {
      immediate: true, // Run on component creation
      handler(newSlug) {
        this.initializeBoard(newSlug);
      }
    }
  },

  methods: {
    async initializeBoard(projectSlug) {
      if (!projectSlug) {
        this.$store.commit('project/SET_CURRENT_PROJECT', null);
        return;
      }

      this.loading = true;

      try {
        // 🚨 CRITICAL CHANGE: Call the new Vuex action that fetches the board data via API
        await this.$store.dispatch('project/fetchProjectBySlug', projectSlug);

      } catch (error) {
        console.error("Failed to load project board from API:", error);
      } finally {
        this.loading = false;
      }
    },

    openTaskModal() {
      // Logic to open a modal for creating a new task
      console.log('Open modal for adding a new task (to be implemented)');
    }
  }
}
</script>

<style scoped>
/* Spinner (Aesthetic for loading state) */
.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-left-color: #4f46e5;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Kanban specific styles */
.board-container {
  /* Adjusted for a better visual on a small screen */
  max-height: calc(100vh - 120px);
}

.kanban-list {
  max-height: 100%;
  overflow-y: auto;
}

.task-card {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.task-card:hover {
  transform: translateY(-1px);
}
</style>