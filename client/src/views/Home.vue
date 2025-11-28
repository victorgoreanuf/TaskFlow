<template>
    <div class="mt-4 mb-6">

      <!-- The list with project's from the user-->
      <ProjectsList></ProjectsList>

      <button @click="modal = true" class="btn-trigger">
        <span>Create New Project</span>
      </button>


      <!-- Modal for creating a modal -->
      <CreateNewProject v-model="modal" title="Create Project" @save="save">
        <div class="input-group">
          <label for="p-name">Project Name</label>

          <input
              id="p-name"
              v-model="projectName"
              type="text"
              class="modal-input"
              placeholder="e.g. Apollo 11"
          >
        </div>
      </CreateNewProject>
    </div>
</template>

<script>

import {SET_BREADCRUMB} from "@/store/breadcrumbs.store";
import CreateNewProject from "./components/modals/CreateNewProject.vue";
import ProjectsList from "@/views/components/project/ProjectsDisplay.vue";
export default {
  components: {ProjectsList, CreateNewProject},
  data() {
    return {
      projectName: "",
      modal: false
    }
  },
  methods:{
    async save() {
      if (!this.projectName.trim()) {
        alert("Please enter a project name");
        return;
      }

      try {
        // This uses your Vuex store exactly like register/login
        await this.$store.dispatch('project/createProject', this.projectName.trim());

        // Success → close modal
        this.modal = false;
        this.projectName = "";

        // IDK why i did that might be stupid
        // await this.$store.dispatch('project/fetchProjects');
        // Later: this.$toast.success(...) or reload projects list

      } catch (error) {
        alert(error.message || "Something went wrong");
      }
    },
  },
  mounted() {
    this.$store.dispatch(SET_BREADCRUMB, [
      {text: this.$t('My Projects'), active: true},
    ]);
  },
}
</script>

<style>
.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem; /* Space between label and input */
}

/* The Label */
.input-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151; /* Dark grey */
}

/* The Beautiful Input */
.modal-input {
  width: 100%;
  padding: 0.75rem 1rem;       /* Comfortable internal spacing */
  font-size: 1rem;
  line-height: 1.5;
  color: #111827;              /* Almost black text */
  background-color: #f9fafb;   /* Very light grey background */
  border: 1px solid #d1d5db;   /* Subtle border */
  border-radius: 0.5rem;       /* Rounded corners */
  transition: all 0.2s ease-in-out; /* Smooth transition */
  outline: none;               /* Remove default browser ugly blue line */
}

/* Placeholder styling */
.modal-input::placeholder {
  color: #9ca3af;
}

/* The "Focus" State - When user clicks inside */
.modal-input:focus {
  background-color: #ffffff;   /* Becomes pure white */
  border-color: #6366f1;       /* Indigo border to match button */
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); /* Soft indigo glow ring */
}

   /* The Main Button Container */
 .btn-trigger {
   /* Layout & Spacing */
   display: inline-flex;
   align-items: center;
   justify-content: center;
   gap: 0.75rem; /* Nice spacing between optional icon and text */
   padding: 0.875rem 1.75rem; /* Substantial padding for a main CTA */

   /* Typography */
   font-size: 1.125rem; /* lg size */
   font-weight: 600;
   line-height: 1;
   color: #ffffff;

   /* Appearance */
   background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); /* Matching indigo gradient */
   border: none;
   border-radius: 0.75rem; /* Matches the modal rounded corners */
   cursor: pointer;

   /* The "Pop" effect */
   box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25), 0 2px 4px -1px rgba(79, 70, 229, 0.15);
   transition: all 0.2s ease-in-out; /* Smooth transitions for everything */
 }

/* Hover State - Lifts up and casts a deeper shadow */
.btn-trigger:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.35), 0 4px 6px -2px rgba(79, 70, 229, 0.25);
  /* Slightly lighter gradient on hover for shine effect */
  background: linear-gradient(135deg, #5b52ee 0%, #4c41db 100%);
}

/* Active State - Clicks down */
.btn-trigger:active {
  transform: translateY(-1px); /* Moves back down slightly */
  box-shadow: 0 5px 10px -3px rgba(79, 70, 229, 0.3);
  background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%); /* Darker when pressed */
}

/* Focus State - For accessibility (keyboard navigation) */
.btn-trigger:focus-visible {
  outline: none;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.4); /* A distinct outer ring */
}

/* Styling for the SVG icon (if you uncomment it) */
.btn-icon {
  width: 1.5rem;  /* w-6 */
  height: 1.5rem; /* h-6 */
  /* Ensure icon color matches text */
  color: inherit;
}
</style>
