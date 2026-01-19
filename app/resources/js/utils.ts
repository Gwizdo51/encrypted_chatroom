export async function generateKeys() {
    const keyPair = await window.crypto.subtle.generateKey(
        {
            name: "RSA-OAEP",
            modulusLength: 2048,
            publicExponent: new Uint8Array([1, 0, 1]), // 65537
            hash: "SHA-256",
        },
        true, // whether the key is extractable (needed to send public key)
        ["encrypt", "decrypt"], // what can be done with the generated keys
    );
    const exportedKey = await window.crypto.subtle.exportKey("jwk", keyPair.publicKey);
    return {
        publicKey: exportedKey,
        privateKey: keyPair.privateKey,
    };
}

export async function encryptMessage(plainMessage: string, recipientPublicKey: JsonWebKey) {
    // 1. Import the recipient's public key back into a usable format
    const publicKey = await window.crypto.subtle.importKey(
        "jwk",
        recipientPublicKey,
        {
            name: "RSA-OAEP",
            hash: "SHA-256",
        },
        true,
        ["encrypt"],
    );
    // 2. Convert the text to a buffer
    const encoder = new TextEncoder();
    const data = encoder.encode(plainMessage);
    // 3. Encrypt it
    const encryptedBuffer = await window.crypto.subtle.encrypt(
        {
            name: "RSA-OAEP"
        },
        publicKey,
        data
    );
    // 4. Convert the buffer to Base64 so it can be sent as a string via Laravel Reverb
    const encryptedMessage = window.btoa(String.fromCharCode(...new Uint8Array(encryptedBuffer)));
    console.log("encrypted message:", encryptedMessage);
    return encryptedMessage;
}

export async function decryptMessage(encryptedMessage: string, userPrivateKey: CryptoKey) {
    // 1. Convert Base64 string back to a buffer
    const binaryString = window.atob(encryptedMessage);
    const buffer = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
        buffer[i] = binaryString.charCodeAt(i);
    }
    // 2. Decrypt using your private key
    const decryptedBuffer = await window.crypto.subtle.decrypt(
        {
            name: "RSA-OAEP"
        },
        userPrivateKey,
        buffer
    );
    // 3. Convert buffer back to a readable string
    const decoder = new TextDecoder();
    const decryptedMessage = decoder.decode(decryptedBuffer);
    return decryptedMessage;
}
