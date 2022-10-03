<script setup>
import {ref} from 'vue';

const keyName = ref('');

const arrayToBase64String = (a) => btoa(String.fromCharCode(...a));

// Decodes a Base64Url string
const base64UrlDecode = (input) => {
  input = input
      .replace(/-/g, '+')
      .replace(/_/g, '/');

  const pad = input.length % 4;
  if (pad) {
    if (pad === 1) {
      throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
    }
    input += new Array(5-pad).join('=');
  }

  return window.atob(input);
};

const getOptions = () => {
    axios
        .post('/webauthn/keys/options')
        .then(response => {
            createKey(response.data.publicKey);
        })
        .catch(console.error);
}

const createKey = (publicKeyCredentialCreationOptions) => {
    console.log(publicKeyCredentialCreationOptions);
    const challenge = Uint8Array.from(base64UrlDecode(publicKeyCredentialCreationOptions.challenge), c => c.charCodeAt(0));
    const userId = Uint8Array.from(window.atob(publicKeyCredentialCreationOptions.user.id), c => c.charCodeAt(0));

    navigator.credentials.create({
        publicKey: {
            ...publicKeyCredentialCreationOptions,
            user: {
                ...publicKeyCredentialCreationOptions.user,
                id: userId,
            },
            challenge: challenge 
        },
    })
    .then(credential => {
        const utf8Decoder = new TextDecoder('utf-8');
        return {
            authenticatorAttachment: credential.authenticatorAttachment,
            id: credential.id,
            rawId: arrayToBase64String(new Uint8Array(credential.rawId)),
            type: credential.type,
            response: {
                clientDataJSON: credential.response.clientDataJSON ?
                    arrayToBase64String(new Uint8Array(credential.response.clientDataJSON)) : null,
                attestationObject: credential.response.attestationObject ?
                    arrayToBase64String(new Uint8Array(credential.response.attestationObject)) : null,
            },
            name: keyName.value,
        }
    })
    .then(AuthAttestationResponse => {
        return axios
            .post('/webauthn/keys', AuthAttestationResponse);
    })
    .then(console.log)
}

</script>
<template>
    <section class="p-4">
        <h2 class="mb-4 text-gray-600 text-xl">Create new Key</h2>
        <div class="flex flex-col gap-4 mb-4">
            <label for="keyname">Key name</label>
            <input name="keyname" id="keyname" type="text" v-model="keyName" class="border border-gray-200 rounded" placeholder="key-name" /> 
        </div>
        <button 
            class="bg-blue-500 hover:bg-blue-600 rounded border-none py-2 px-4 text-white"
            @click="getOptions"
        >
            Create
        </button>
    </section>
</template>
