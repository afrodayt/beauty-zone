const enumLabels = {
  NEW: "Новый",
  ACTIVE: "Активный",
  LOST: "Потерян",
  SCHEDULED: "Запланирован",
  ARRIVED: "Пришел",
  CANCELED: "Отменен",
  NO_SHOW: "Не пришел",
  CASH: "Наличные",
  CARD: "Карта",
  TRANSFER: "Перевод",
  PACKAGE: "Пакет",
  EXPIRED: "Истек",
  EXHAUSTED: "Израсходован",
  RENT: "Аренда",
  SALARY: "Зарплата",
  SUPPLIES: "Расходники",
  MARKETING: "Маркетинг",
  OTHER: "Другое",
};

export function enumLabel(value) {
  if (value === null || value === undefined || value === "") {
    return "-";
  }

  return enumLabels[value] ?? value;
}
