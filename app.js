// 1. IMPORTACIONES
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";

import {
    getFirestore,
    collection,
    addDoc,
    getDocs,
    deleteDoc,
    doc,
    updateDoc
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

// 2. CONFIGURACIÓN DE FIREBASE (Actualizado con tus datos de la imagen)
const firebaseConfig = {
    apiKey: "AIzaSyDcu2m3TyTGodGhrMZfmGkezho4hv7nWIk",
    authDomain: "crud-firebase-sem10.firebaseapp.com",
    projectId: "crud-firebase-sem10",
    storageBucket: "crud-firebase-sem10.firebasestorage.app",
    messagingSenderId: "1072311865400",
    appId: "1:1072311865400:web:33bd9aeda86220331316cd"
};

// 3. INICIALIZAR FIREBASE
const app = initializeApp(firebaseConfig);
const db = getFirestore(app);

// 4. RESTO DEL CÓDIGO (CRUD)
let datos = [];

window.agregar = async function () {
    const nombre = document.getElementById("nombre").value;
    const precio = document.getElementById("precio").value;

    if (nombre === "" || precio === "") {
        alert("Completa todos los campos");
        return;
    }

    await addDoc(collection(db, "productos"), {
        nombre,
        precio
    });

    alert("Producto agregado");

    document.getElementById("nombre").value = "";
    document.getElementById("precio").value = "";

    leer();
};

async function leer() {
    datos = [];
    const querySnapshot = await getDocs(collection(db, "productos"));
    querySnapshot.forEach((docu) => {
        datos.push({
            id: docu.id,
            ...docu.data()
        });
    });
    mostrar(datos);
}

function mostrar(lista) {
    const tabla = document.getElementById("tabla");
    tabla.innerHTML = "";

    lista.forEach(d => {
        tabla.innerHTML += `
            <tr>
                <td>${d.nombre}</td>
                <td>$${d.precio}</td>
                <td>
                    <button onclick="eliminar('${d.id}')">Eliminar</button>
                    <button onclick="editar('${d.id}')">Editar</button>
                </td>
            </tr>
        `;
    });
}

window.eliminar = async function (id) {
    await deleteDoc(doc(db, "productos", id));
    leer();
};

window.editar = async function (id) {
    const nuevoNombre = prompt("Nuevo nombre:");
    const nuevoPrecio = prompt("Nuevo precio:");

    if (!nuevoNombre || !nuevoPrecio) return;

    await updateDoc(doc(db, "productos", id), {
        nombre: nuevoNombre,
        precio: nuevoPrecio
    });

    leer();
};

window.filtrar = function () {
    const texto = document.getElementById("buscar").value.toLowerCase();
    const filtrados = datos.filter(d =>
        d.nombre.toLowerCase().includes(texto)
    );
    mostrar(filtrados);
};

leer();
