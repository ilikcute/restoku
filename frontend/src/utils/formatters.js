/**
 * Shared formatting and download helpers for Vue frontend.
 */

export function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

export function formatDate(dateStr, includeTime = false) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  const options = {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  };
  if (includeTime) {
    options.hour = '2-digit';
    options.minute = '2-digit';
  }
  return d.toLocaleDateString('id-ID', options);
}

export function formatDateId(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });
}

export function formatDatePayload(date) {
  if (!date) return null;
  const d = new Date(date);
  let month = '' + (d.getMonth() + 1);
  let day = '' + d.getDate();
  const year = d.getFullYear();
  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;
  return [year, month, day].join('-');
}

export function downloadBlob(blobData, filename) {
  const downloadUrl = window.URL.createObjectURL(new Blob([blobData]));
  const link = document.createElement('a');
  link.href = downloadUrl;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(downloadUrl);
}

