# 🚀 Deploy automático PHP a SWPanel vía FTP

Este proyecto está configurado para que **cada push a la rama `main` (o `master`) despliegue automáticamente los archivos PHP al servidor SWPanel vía FTP**, usando **GitHub Actions**.

---

## 📌 Requisitos previos

1. Tener el proyecto en **GitHub**.  
2. Usuario y clave **FTP** del hosting en SWPanel.  
3. Conocer la ruta de la carpeta pública en tu hosting (ej: `/datos/web/`, `/httpdocs/`, `/public_html/`).  

---

## ⚙️ Configuración de GitHub Actions

1. Crear el archivo:  
   `.github/workflows/deploy.yml`

   ```yaml
   name: 🚀 Deploy PHP via FTP

   on:
     push:
       branches:
         - main   # cada push a la rama main hará el deploy

   jobs:
     ftp-deploy:
       runs-on: ubuntu-latest
       steps:
         # 1. Descargar el repo
         - name: Checkout
           uses: actions/checkout@v3

         # 2. Subir a tu hosting por FTP
         - name: FTP Deploy
           uses: SamKirkland/FTP-Deploy-Action@v4.3.4
           with:
             server: 81.25.112.54
             protocol: ftp
             username: ${{ secrets.FTP_USER }}
             password: ${{ secrets.FTP_PASS }}
             local-dir: ./             # carpeta local (repo)
             server-dir: /datos/web/   # carpeta en tu hosting
   ```

2. En GitHub, ir a:  
   **Settings → Secrets and variables → Actions** y agregar:  

   - `FTP_USER` → tu usuario FTP  
   - `FTP_PASS` → tu contraseña FTP  

---

## 🚀 Uso

1. Hacer cambios en el proyecto local.  
2. Subirlos a GitHub:  
   ```bash
   git add .
   git commit -m "update"
   git push origin main
   ```
3. GitHub ejecutará el workflow y subirá los archivos al servidor.  
4. Verificar en la pestaña **Actions** de GitHub:  
   - ✅ Verde = deploy exitoso  
   - ❌ Rojo = error (abrir logs del job para ver detalle)  

---

## 📂 Logs y verificación

- Si no ves cambios en tu web:  
  - Verifica la ruta en `server-dir` (`/datos/web/`, `/httpdocs/`, etc.).  
  - Revisa permisos del usuario FTP.  
- En **Actions → Job → FTP Deploy** puedes ver el log completo del despliegue.  

---

## 🔒 Notas importantes

- Para el **primer despliegue** o limpieza total puedes añadir:  

  ```yaml
  dangerous-clean-slate: true
  ```

  en la configuración de `FTP Deploy`.  
  ⚠️ Esto borra todo en la carpeta del servidor antes de subir.  

---

✅ Con esta configuración tu proyecto queda con **deploy continuo vía GitHub Actions + FTP**.  
Cada vez que hagas `git push`, tu web se actualizará sola en el servidor SWPanel.  

