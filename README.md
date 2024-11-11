
# AI Content Auto Rewriter

**Plugin neve**: AI Content Auto Rewriter  
**Verzió**: 3.71  
**Leírás**: Ez a WordPress plugin automatikusan újraírja az RSS feedekből érkező tartalmakat az OpenAI API segítségével, ezáltal egyedi szöveget hozva létre.

## Jellemzők
- **API kulcs biztonságos kezelése**: Az API kulcs titkosítva van tárolva a szerveren, így biztosítva annak biztonságát.
- **Testreszabható modellek és paraméterek**: Választható OpenAI modellek, mint például GPT-4, gpt-3.5-turbo, valamint beállítható hőmérséklet és token limit.
- **Integráció a WP All Import pluginnal**: Könnyen illeszthető az importálási folyamatokhoz, így automatizáltan újraírásra kerülnek az importált tartalmak.
- **Automatikus frissítésfigyelés**: A plugin frissítését egy külön fájl ellenőrzi, és értesítést küld, ha új verzió érhető el.

## Telepítés
1. Töltsd le a plugin fájlt, és töltsd fel a `wp-content/plugins` könyvtárba.
2. Aktiváld a plugint a WordPress admin felületén a **Bővítmények** menüpont alatt.

## Beállítások
A plugin beállításai az admin felületen, az **AI Rewriter** menüpont alatt találhatók. Itt megadhatod az API kulcsot, választhatsz AI modellt, beállíthatod a hőmérsékletet és egyéb opciókat.

### Fő Beállítások
- **API Kulcs**: Kötelező mező, amely az OpenAI API kulcsát tárolja titkosítva.
- **AI Modell**: Választható modellek az OpenAI API-ból, mint a GPT-3.5 és GPT-4.
- **Temperature**: Az újraírás változatosságának mértéke; a 0.7 alapértelmezett.
- **Max Tokens**: A generált szöveg maximális token (szómennyiség) korlátja.

## Frissítésfigyelés
A plugin automatikusan ellenőrzi a GitHub távoli verziószámát, és értesítést küld a frissítés elérhetőségéről.

## Licenc
Ez a plugin a MIT Licenc alatt érhető el. A plugin használatához szükség lehet az OpenAI licencfeltételeinek átolvasására, különösen az API használata esetén.
