function carousel() {
    return {
      current: 0,
      images: [
        { src: '../assets/images/dodol-talas-khas-bogor-1951607336_upscayl_2x_ultramix-balanced-4x.png', alt: 'Dodol Talas' },
        { src: '../assets/images/5189bba8-bdf0-4d65-97fa-f0c1fbbace1e-2311046643_upscayl_2x_ultramix-balanced-4x.png', alt: 'Kue Lapis' },
        { src: '../assets/images/601a47c7b5d32.jpg', alt: 'Bakpia' },
        { src: '../assets/images/kue-lapis-talas.jpg', alt: 'Kue Lapis Talas' }
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