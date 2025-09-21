<?php
// Configuration Google Analytics et autres outils de tracking
?>

<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  
  // Remplacez GA_MEASUREMENT_ID par votre vrai ID Google Analytics
  gtag('config', 'GA_MEASUREMENT_ID', {
    page_title: document.title,
    page_location: window.location.href,
    custom_map: {
      'custom_parameter_1': 'page_type'
    }
  });
  
  // Événements personnalisés pour le suivi des conversions
  function trackContactForm() {
    gtag('event', 'contact_form_submit', {
      'event_category': 'engagement',
      'event_label': 'contact_form'
    });
  }
  
  function trackQuoteRequest() {
    gtag('event', 'quote_request', {
      'event_category': 'conversion',
      'event_label': 'quote_form'
    });
  }
  
  function trackServiceView(serviceName) {
    gtag('event', 'service_view', {
      'event_category': 'engagement',
      'event_label': serviceName
    });
  }
</script>

<!-- Google Search Console Verification -->
<meta name="google-site-verification" content="VOTRE_CODE_VERIFICATION_GSC" />

<!-- Bing Webmaster Tools Verification -->
<meta name="msvalidate.01" content="VOTRE_CODE_VERIFICATION_BING" />

<!-- Facebook Pixel (optionnel) -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');

// Remplacez VOTRE_PIXEL_ID par votre vrai Facebook Pixel ID
fbq('init', 'VOTRE_PIXEL_ID');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=VOTRE_PIXEL_ID&ev=PageView&noscript=1"
/></noscript>

<!-- Schema.org pour le référencement local -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Universe Security",
  "image": "https://universe-security.ci/img/logo universe security.jpg",
  "telephone": "+225-0101012501",
  "email": "info@universe-security.ci",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Cocody Angré 8ième tranche",
    "addressLocality": "Abidjan",
    "addressCountry": "CI"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 5.3599517,
    "longitude": -3.9810768
  },
  "url": "https://universe-security.ci",
  "sameAs": [
    "https://www.facebook.com/universesecurity",
    "https://www.linkedin.com/company/universe-security",
    "https://twitter.com/universesecurity"
  ],
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Monday",
      "Tuesday", 
      "Wednesday",
      "Thursday",
      "Friday"
    ],
    "opens": "08:00",
    "closes": "18:00"
  }
}
</script>
