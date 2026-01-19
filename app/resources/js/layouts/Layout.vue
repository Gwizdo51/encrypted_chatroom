<script setup lang="ts">
import { User } from "@/types";
import { Head } from "@inertiajs/vue3";

// props
const {user} = defineProps<{
    user: User | null;
}>();
</script>


<template>
    <Head title="Encrypted chatroom POC" />
    <div class="flex flex-col h-svh bg-gray-950">
        <!-- Chat header -->
        <header class="px-4 py-3 bg-gray-900 border-b border-gray-800 flex items-center">
            <h1 class="text-lg font-semibold text-gray-100 flex-1">Encrypted chatroom</h1>
            <div v-if="user" class="text-gray-500">
                Connected as: <span class="font-bold text-gray-100">{{ user.name }}</span>
            </div>
        </header>
        <slot />
    </div>
</template>
