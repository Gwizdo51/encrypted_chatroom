<script setup lang="ts">
import { ref, onMounted } from "vue";
import Layout from "@/layouts/Layout.vue";
import Chatroom from "@/components/my_components/Chatroom.vue";
import { User } from "@/types";
import { generateKeys } from "@/utils";

const isLoading = ref<boolean>(true);
const isConnecting = ref<boolean>(false);
const isConnected = ref<boolean>(false);
const ownPublicKey = ref<JsonWebKey | null>(null);
const ownPrivateKey = ref<CryptoKey | null>(null);
const user = ref<User | null>(null);
const otherUser = ref<User | null>(null);

async function handleConnect(userName: string) {
    console.log(`Connecting as ${userName}`);
    isConnecting.value = true;
    const response = await fetch("/api/connect", {
        method: "post",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Accept": "application/json; charset=utf-8"
        },
        body: JSON.stringify({
            userName,
            publicKey: <JsonWebKey>ownPublicKey.value,
        }),
    });
    isConnecting.value = false;
    if (response.ok) {
        const jsonResponse = await response.json();
        console.log("connect json response:", jsonResponse);
        // set the user and otherUser info
        user.value = {
            name: userName,
            publicKey: <JsonWebKey>ownPublicKey.value,
        };
        otherUser.value = {
            name: jsonResponse.otherUser.name,
            publicKey: JSON.parse(jsonResponse.otherUser.publicKey),
        };
        isConnected.value = true;
    }
    else if (response.status === 422) {
        const jsonResponse = await response.json();
        console.error("connect json response:", jsonResponse);
    }
    else {
        console.error("The request failed for an unknown reason.", response);
    }
}

function updateOtherUserPublicKey(newPublicKey: JsonWebKey) {
    otherUser.value!.publicKey = newPublicKey;
}

// onMounted(async () => {
//     // generate a new RSA keypair for this session
//     const generatedKeys = await generateKeys();
//     ownPublicKey.value = generatedKeys.publicKey;
//     ownPrivateKey.value = generatedKeys.privateKey;
//     // console.log("public key:", OwnPublicKey.value);
//     // console.log("private key:", OwnPrivateKey.value);
//     isLoading.value = false;
//     // test crypto
//     // const originalMessage = "hello boys !!";
//     // const encryptedMessage = await encryptMessage(originalMessage, ownPublicKey.value);
//     // console.log("encrypted message:", encryptMessage);
//     // const decryptedMessage = await decryptMessage(encryptedMessage, ownPrivateKey.value);
//     // console.log("decrypted message:", decryptedMessage);
// });

async function generateSessionKeys() {
    // generate a new RSA keypair for this session
    const generatedKeys = await generateKeys();
    console.log("session keys generated");
    ownPublicKey.value = generatedKeys.publicKey;
    ownPrivateKey.value = generatedKeys.privateKey;
    // console.log("public key:", OwnPublicKey.value);
    // console.log("private key:", OwnPrivateKey.value);
    isLoading.value = false;
}
generateSessionKeys();
</script>


<template>
    <Layout :user="user">
        <div v-if="isLoading" class="flex-1 flex justify-center items-center text-xl">
            Generating keys ...
        </div>
        <div v-else-if="!isConnected" class="flex-1 flex justify-center items-center gap-3">
            <button
                v-for="availableUser in ['Alice', 'Bob']"
                class="p-5 bg-gray-800 hover:bg-gray-600 active:bg-gray-900 disabled:bg-gray-900 rounded-full transition-all ease-out duration-100"
                :disabled="isConnecting"
                @click="handleConnect(availableUser)"
            >
                Connect as {{ availableUser }}
            </button>
        </div>
        <Chatroom
            v-else
            :user="user!"
            :user-private-key="ownPrivateKey!"
            :other-user="otherUser!"
            @new-other-user-public-key="updateOtherUserPublicKey"
        />
    </Layout>
</template>
