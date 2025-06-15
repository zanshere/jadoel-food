function carousel() {
  const baseURL = document.getElementById('app').dataset.baseurl;

  return {
    current: 0,
    images: [
      { src: baseURL + 'assets/images/dodol-talas.png', alt: 'Dodol Talas' },
      { src: baseURL + 'assets/images/lapis-legit.jpg', alt: 'Lapis Legit' },
      { src: baseURL + 'assets/images/bakpia-jogja.png', alt: 'Bakpia' },
      { src: baseURL + 'assets/images/kue-lapis-talas.jpg', alt: 'Kue Lapis Talas' }
    ],
    start() {
      setInterval(() => this.next(), 5000);
    },
    next() {
      this.current = (this.current + 1) % this.images.length;
    },
    prev() {
      this.current = (this.current - 1 + this.images.length) % this.images.length;
    }
  }
}
