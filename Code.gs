/**
 * ============================================================================
 * GOOGLE APPS SCRIPT BACKEND - AI MARKETING STUDIO UMKM
 * ============================================================================
 * Aplikasi ini berjalan native di lingkungan Google Workspace / Google One.
 * - Tanpa API Login pihak ketiga (otomatis membaca akun Google aktif)
 * - Tanpa input API Key dari user (AI diproses via script backend / client engine)
 * ============================================================================
 */

/**
 * 1. Entry point saat Web App dibuka di browser
 */
function doGet(e) {
  var candidates = ['Index', 'index', 'ai_marketing_studio_v2', 'ai_marketing_studio_umkm'];
  if (e && e.parameter && e.parameter.file) {
    candidates.unshift(e.parameter.file);
  }
  
  var htmlOutput = null;
  for (var i = 0; i < candidates.length; i++) {
    try {
      htmlOutput = HtmlService.createHtmlOutputFromFile(candidates[i]);
      break;
    } catch (err) {
      // continue to next candidate
    }
  }
  
  if (!htmlOutput) {
    return HtmlService.createHtmlOutput('<h3>Error: File HTML tidak ditemukan di Google Apps Script. Pastikan ada file bernama "Index".</h3>');
  }

  return htmlOutput
    .setTitle('AI Marketing Studio UMKM - Google One')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1.0')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

/**
 * 2. Mengambil profil akun Google yang sedang membuka Web App
 * Dipanggil secara otomatis oleh frontend melalui:
 * google.script.run.getGoogleSessionUser()
 */
function getGoogleSessionUser() {
  try {
    var activeUser = Session.getActiveUser();
    var email = activeUser.getEmail();
    
    // Fallback jika dibuka dalam konteks preview editor
    if (!email) {
      email = Session.getEffectiveUser().getEmail() || 'partner@umkm.co.id';
    }

    var username = email.split('@')[0];
    var domain = email.split('@')[1] || '';
    
    // Auto-detect tier Google One berdasarkan akun
    var tierId = 'standard'; // Default 7.500 kredit
    if (domain && domain !== 'gmail.com') {
      tierId = 'premium'; // Google Workspace Bisnis / Kantor • 25.000 kredit
    } else if (email.indexOf('pro') !== -1 || email.indexOf('one') !== -1 || email.indexOf('studio') !== -1) {
      tierId = 'ai'; // Google One AI Premium • 100.000 kredit
    }

    // Cek apakah ada tier atau kredit yang tersimpan khusus user ini
    var userProps = PropertiesService.getUserProperties();
    var savedTier = userProps.getProperty('g1_tier');
    if (savedTier) {
      tierId = savedTier;
    }
    
    var savedCredits = userProps.getProperty('credits');
    var credits = savedCredits ? parseInt(savedCredits, 10) : null;

    // Bersihkan nama pengguna dari email
    var cleanName = username
      .replace(/[\._]/g, ' ')
      .replace(/\b\w/g, function(l) { return l.toUpperCase(); });

    return {
      email: email,
      name: cleanName,
      givenName: cleanName.split(' ')[0],
      domain: domain,
      tierId: tierId,
      credits: credits
    };
  } catch (err) {
    return {
      email: 'user@umkm.co.id',
      name: 'Pengguna Google UMKM',
      givenName: 'UMKM',
      tierId: 'standard',
      credits: 7500,
      error: err.toString()
    };
  }
}

/**
 * 3. Simpan perubahan tier dan kredit pengguna ke Google UserProperties
 */
function saveUserTierAndCredits(tierId, credits) {
  try {
    var userProps = PropertiesService.getUserProperties();
    if (tierId) userProps.setProperty('g1_tier', tierId);
    if (credits !== undefined && credits !== null) {
      userProps.setProperty('credits', credits.toString());
    }
    return { success: true };
  } catch (e) {
    return { success: false, error: e.toString() };
  }
}

/**
 * 4. Server-Side Gemini AI Engine Proxy (Opsional)
 * Jika admin mengisi GEMINI_API_KEY di Project Settings > Script Properties,
 * seluruh user dapat generate AI langsung tanpa perlu memasukkan API key!
 */
function callGeminiServer(modelName, promptText, imageBase64, mimeType) {
  try {
    var apiKey = PropertiesService.getScriptProperties().getProperty('GEMINI_API_KEY');
    
    // Jika admin belum mengisi GEMINI_API_KEY, kembalikan sinyal agar client engine berjalan
    if (!apiKey) {
      return {
        status: 'simulated',
        message: 'Menggunakan engine client-side Google Nano Banana & Flow Veo 3.'
      };
    }

    var model = modelName || 'gemini-2.5-flash';
    var url = 'https://generativelanguage.googleapis.com/v1beta/models/' + model + ':generateContent?key=' + apiKey;

    var parts = [];
    if (imageBase64) {
      parts.push({
        inlineData: {
          mimeType: mimeType || 'image/jpeg',
          data: imageBase64
        }
      });
    }
    parts.push({ text: promptText });

    var payload = {
      contents: [{ parts: parts }]
    };

    var options = {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    };

    var response = UrlFetchApp.fetch(url, options);
    var json = JSON.parse(response.getContentText());

    if (json.candidates && json.candidates[0] && json.candidates[0].content && json.candidates[0].content.parts[0]) {
      return {
        status: 'success',
        text: json.candidates[0].content.parts[0].text
      };
    }

    return { status: 'error', raw: json };
  } catch (err) {
    return { status: 'error', error: err.toString() };
  }
}
