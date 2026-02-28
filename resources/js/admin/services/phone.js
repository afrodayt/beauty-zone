export function phoneToTelHref(phone) {
  const raw = String(phone || "").trim();

  if (!raw) {
    return "";
  }

  const hasLeadingPlus = raw.startsWith("+");
  const digits = raw.replace(/\D/g, "");

  if (!digits) {
    return "";
  }

  return hasLeadingPlus ? `tel:+${digits}` : `tel:${digits}`;
}
