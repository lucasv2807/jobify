# 🔧 Linters de PHP para Jobify

Se han instalado **PHP_CodeSniffer** y **PHP CS Fixer** como linters para el proyecto, similares a ESLint para JavaScript.

## 🚀 Instalación completada

✅ **PHP_CodeSniffer** - Para verificar código
✅ **PHP CS Fixer** - Para corregir automáticamente
✅ Configuración PSR-12 (estándar moderno de PHP)
✅ Scripts de Composer configurados

## 📋 Comandos disponibles

### Verificar código (como `eslint`)

```bash
# Revisar todo el código y mostrar errores
composer lint

# Revisar con reporte resumido
composer check
```

### Corregir automáticamente (como `eslint --fix`)

```bash
# Ver qué se corregiría (dry-run)
composer lint:check

# Aplicar las correcciones automáticamente
composer lint:fix
```

### Uso directo de las herramientas

```bash
# PHP_CodeSniffer directo
./vendor/bin/phpcs src/
./vendor/bin/phpcs --standard=PSR12 src/controller/Usuario.php

# PHP CS Fixer directo
./vendor/bin/php-cs-fixer fix --dry-run --diff
./vendor/bin/php-cs-fixer fix
```

## 📁 Archivos configurados

- **`phpcs.xml`** - Configuración de PHP_CodeSniffer
- **`.php-cs-fixer.php`** - Configuración de PHP CS Fixer
- **`composer.json`** - Scripts agregados

## 🔍 Qué revisan los linters

### Errores que detectaron en tu código

- **Indentación**: Usar 4 espacios en lugar de 2
- **Arrays**: Usar sintaxis corta `[]` en lugar de `array()`
- **Espacios**: Líneas en blanco después de `<?php`
- **Namespaces**: Las clases deberían estar en namespaces
- **Líneas largas**: Máximo 120-150 caracteres por línea
- **else if vs elseif**: Usar `elseif` en lugar de `else if`

### Reglas principales

- ✅ **PSR-12** - Estándar de código PHP
- ✅ **Sintaxis de arrays corta** - `[]` en lugar de `array()`
- ✅ **Comillas simples** - Para strings sin variables
- ✅ **Espaciado consistente**
- ✅ **Indentación de 4 espacios**

## 🛠️ Workflow recomendado

1. **Antes de hacer commit:**

   ```bash
   composer lint:fix  # Corrige automáticamente
   composer lint      # Verifica que no queden errores
   ```

2. **Durante desarrollo:**

   ```bash
   composer lint:check  # Ver qué se corregiría sin aplicar
   ```

## 🎯 Siguiente paso

**¿Quieres que aplique las correcciones automáticas ahora?**

Ejecuta:

```bash
composer lint:fix
```

Esto arreglará automáticamente la mayoría de problemas (indentación, sintaxis de arrays, espacios, etc.).

---

**¡Tu proyecto PHP ahora tiene linting automático como ESLint! 🎉**
