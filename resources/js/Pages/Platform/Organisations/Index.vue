<template>
  <div class="container py-4">
    <Head title="Platform" />
    <h1 class="h4 mb-4">Platform organisations</h1>
    <div class="card border-0 shadow-sm rounded-4">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Name</th>
              <th>Plan</th>
              <th>Status</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="org in organisations" :key="org.id">
              <td>{{ org.trading_name || org.name }}</td>
              <td>{{ org.plan_key }}</td>
              <td>{{ org.plan_status }}</td>
              <td>{{ new Date(org.created_at).toLocaleDateString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({ organisations: Array });
</script>
