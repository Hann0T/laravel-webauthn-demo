<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WebAuthn from '@/Components/WebAuthn.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { onMounted } from 'vue';

const props = defineProps(['webauthnKeys']);

const deleteKey = (id) => {
    console.log('keyId: ', id);
    axios
        .delete(`/webauthn/keys/${id}`)
        .then(console.log)
        .catch(console.error);
}

onMounted(() => {
    if(props.webauthnKeys.length > 0) {
        axios
            .get('/webauthn/remember')
            .then(response => console.log('remember: ', response.data))
            .catch(console.error);
    }
})

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex flex-col gap-4 p-6 bg-white border-b border-gray-200">
                        <h3 class="text-gray-800 text-xl">Keys: </h3>
                        <div v-for="key in webauthnKeys" class="flex gap-4 items-center">
                            <span>Id: {{ key.id }}</span>
                            <span>Name: {{ key.name }}</span>
                            <span>Type: {{ key.type }}</span>
                            <span>Last Active: {{ key.last_active }}</span>
                            <button @click="deleteKey(key.id)" class="bg-red-500 hover:bg-red-600 py-2 px-4 rounded text-white">
                                Delete
                            </button>
                        </div>
                    </div>
                    <WebAuthn />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
