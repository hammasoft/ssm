import { formatPrice, timeAgo } from './format';

describe('formatPrice', () => {
  it('formate en euros à la française', () => {
    expect(formatPrice(24.79)).toBe('24,79 €');
    expect(formatPrice(1250)).toBe('1 250,00 €');
  });

  it('renvoie une chaîne vide si la valeur est absente', () => {
    expect(formatPrice(undefined)).toBe('');
  });
});

describe('timeAgo', () => {
  it('exprime une durée relative', () => {
    const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000).toISOString();
    expect(timeAgo(fiveMinutesAgo)).toBe('il y a 5 min');
    expect(timeAgo(new Date().toISOString())).toBe("à l'instant");
  });
});
