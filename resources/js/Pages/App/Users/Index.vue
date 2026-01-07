<template>
  <div class="container py-4">
    <Head title="Users" />
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h4 mb-0">Team & roles</h1>
        <p class="text-muted mb-0">Invite org owners, shop managers, and staff.</p>
      </div>
      <button class="btn btn-primary" @click="showInvite = !showInvite">Invite user</button>
    </div>

    <div v-if="showInvite" class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body">
        <form @submit.prevent="invite">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Name</label>
              <input class="form-control" v-model="inviteForm.name" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" v-model="inviteForm.email" required />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-select" v-model="inviteForm.role">
              <option value="shop_manager">Shop manager</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <button class="btn btn-success" type="submit" :disabled="inviteForm.processing">Send invite</button>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.role }}</td>
              <td>
                <select class="form-select form-select-sm w-auto" :value="user.status" @change="updateStatus(user.id, $event.target.value)">
                  <option value="active">Active</option>
                  <option value="disabled">Disabled</option>
                </select>
              </td>
              <td>
                <button class="btn btn-link p-0" @click="resend(user.id)">Resend invite</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ users: Array });
const showInvite = ref(false);
const inviteForm = useForm({ name: '', email: '', role: 'staff', shop_ids: [] });
const invite = () => inviteForm.post(route('app.users.store'), { onSuccess: () => inviteForm.reset('name', 'email') });
const updateStatus = (id, status) => router.patch(route('app.users.status', id), { status });
const resend = id => router.post(route('app.users.resend', id));
</script>
